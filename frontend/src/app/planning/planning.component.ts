import { Component, OnInit } from '@angular/core';
import { CalendarOptions } from '@fullcalendar/core';
import dayGridPlugin from '@fullcalendar/daygrid';
import timeGridPlugin from '@fullcalendar/timegrid';
import interactionPlugin from '@fullcalendar/interaction';
import { SessionService } from '../services/session.service';
import { CoachService } from '../services/coach.service';
import { Session } from '../models/session';
import { switchMap } from 'rxjs';
import { AuthService } from '../services/auth.service';
import { Router } from '@angular/router';

@Component({
  selector: 'app-planning',
  templateUrl: './planning.component.html',
  styleUrls: ['./planning.component.css']
})
export class PlanningComponent implements OnInit {
  sessions: Session[] = [];
  filteredSessions: Session[] = [];
  selectedSession: Session | null = null;
  isLoading = false;
  errorMessage = '';
  isAuthenticated = false;
  showOnlyMyRegistrations = false;
  userId: number | null = null;
  
  calendarOptions: CalendarOptions = {
    plugins: [dayGridPlugin, timeGridPlugin, interactionPlugin],
    initialView: 'timeGridWeek',
    headerToolbar: {
      left: 'prev,next today',
      center: 'title',
      right: 'dayGridMonth,timeGridWeek,timeGridDay'
    },
    events: [],
    eventClick: this.handleEventClick.bind(this),
    height: 'auto',
    allDaySlot: false,
    slotMinTime: '07:00:00',
    slotMaxTime: '22:00:00',
    locale: 'fr',
    buttonText: {
      today: "Aujourd'hui",
      month: 'Mois',
      week: 'Semaine',
      day: 'Jour'
    },
    timeZone: 'local',
    eventTimeFormat: {
      hour: '2-digit',
      minute: '2-digit',
      meridiem: false,
      hour12: false
    },
    dayHeaderClassNames: (arg) => {
      if (arg.date) {
        return arg.date.getDay() === 0 ? 'fc-day-sunday' : '';
      }
      return '';
    },
    dayCellClassNames: (arg) => {
      if (arg.date) {
        return arg.date.getDay() === 0 ? 'fc-day-sunday' : '';
      }
      return '';
    },
    selectConstraint: {
      daysOfWeek: [1, 2, 3, 4, 5, 6]
    },
    slotLabelClassNames: (arg) => {
      if (arg.date) {
        return arg.date.getDay() === 0 ? 'fc-day-sunday' : '';
      }
      return '';
    },
    slotLaneClassNames: (arg) => {
      if (arg.date) {
        return arg.date.getDay() === 0 ? 'fc-day-sunday' : '';
      }
      return '';
    }
  };

  constructor(
    private sessionService: SessionService,
    private coachService: CoachService,
    private authService: AuthService,
    private router: Router
  ) { }

  ngOnInit(): void {
    // Vérifier si l'utilisateur est connecté
    this.checkAuthentication();
  }

  // Vérifier si l'utilisateur est connecté
  checkAuthentication(): void {
    this.isAuthenticated = this.authService.currentAuthUserValue.isLogged();
    
    if (!this.isAuthenticated) {
      this.errorMessage = 'Vous devez être connecté pour accéder au planning des séances.';
      // Rediriger vers la page de connexion après un court délai
      setTimeout(() => {
        this.router.navigate(['/login'], { 
          queryParams: { returnUrl: '/planning' } 
        });
      }, 3000);
    } else {
      // Si l'utilisateur est connecté, récupérer son ID
      this.userId = this.authService.currentAuthUserValue.id;
      // Rafraîchir les informations et charger les séances
      this.refreshUserInfo();
      this.loadSessions();
    }
  }

  // Rafraîchir les informations utilisateur
  refreshUserInfo(): void {
    this.authService.refreshUserInfo().subscribe({
      next: (success) => {
        console.log('Rafraîchissement des informations utilisateur:', success ? 'réussi' : 'échoué');
        // Vérifier à nouveau l'authentification après le rafraîchissement
        this.isAuthenticated = this.authService.currentAuthUserValue.isLogged();
        
        if (!this.isAuthenticated) {
          this.errorMessage = 'Votre session a expiré. Veuillez vous reconnecter.';
          setTimeout(() => {
            this.router.navigate(['/login'], { 
              queryParams: { returnUrl: '/planning' } 
            });
          }, 3000);
        }
      },
      error: (error) => {
        console.error('Erreur lors du rafraîchissement des informations utilisateur:', error);
        this.errorMessage = 'Erreur lors de la vérification de votre session. Veuillez vous reconnecter.';
      }
    });
  }

  loadSessions(): void {
    this.isLoading = true;
    this.errorMessage = '';
    
    this.sessionService.getSessions().pipe(
      switchMap(sessions => this.sessionService.enrichSessionsWithCoachNames(sessions))
    ).subscribe({
      next: (sessions) => {
        console.log('Sessions enrichies avec les noms des coachs:', sessions);
        this.sessions = sessions;
        this.filterSessions();
        this.isLoading = false;
      },
      error: (error) => {
        console.error('Erreur lors du chargement des séances:', error);
        this.errorMessage = 'Impossible de charger les séances. Veuillez réessayer plus tard.';
        this.isLoading = false;
      }
    });
  }

  // Filtrer les sessions selon le mode d'affichage
  filterSessions(): void {
    if (this.showOnlyMyRegistrations) {
      // Filtrer pour n'afficher que les séances où l'utilisateur est inscrit
      this.filteredSessions = this.sessions.filter(session => {
        // Vérifier si l'utilisateur est inscrit à cette séance
        if (session.isUserRegistered) {
          return true;
        }
        
        // Vérifier dans la liste des participants si c'est un tableau
        if (Array.isArray(session.participants)) {
          return session.participants.some(p => p.id === this.userId);
        }
        
        // Vérifier dans la liste des sportifs si disponible
        if (Array.isArray(session.sportifs)) {
          return session.sportifs.some(sportif => sportif.id === this.userId);
        }
        
        return false;
      });
    } else {
      // Afficher toutes les séances
      this.filteredSessions = [...this.sessions];
    }
    this.updateCalendarEvents();
  }

  // Basculer entre "Toutes les séances" et "Mon planning"
  toggleMyRegistrations(): void {
    this.showOnlyMyRegistrations = !this.showOnlyMyRegistrations;
    this.filterSessions();
  }

  updateCalendarEvents(): void {
    const events = this.filteredSessions.map(session => {
      // Formater l'heure pour l'affichage
      const startTime = this.formatTime(session.start);
      const endTime = this.formatTime(session.end);
      
      // Déterminer le style en fonction du statut
      let textColor = '#fff';
      let opacity = 1;
      
      if (session.statut === 'annulee') {
        opacity = 0.6;
        textColor = '#f44336';
      } else if (session.statut === 'terminee') {
        opacity = 0.8;
      }
      
      // Ajouter une bordure spéciale pour les séances où l'utilisateur est inscrit
      const borderColor = session.isUserRegistered ? '#FFD700' : undefined; // Bordure dorée pour les inscriptions
      
      // Ajouter une classe CSS spéciale pour les séances où l'utilisateur est inscrit
      const classNames = session.isUserRegistered ? ['fc-event-registered'] : [];
      
      const event = {
        id: session.id.toString(),
        title: session.title,
        start: session.start,
        end: session.end,
        color: session.color,
        textColor: textColor,
        opacity: opacity,
        borderColor: borderColor,
        classNames: classNames,
        extendedProps: {
          description: session.description,
          coachName: session.coachName,
          currentParticipants: session.currentParticipants,
          startTime: startTime,
          endTime: endTime,
          statut: session.statut,
          isUserRegistered: session.isUserRegistered
        }
      };
      console.log('Événement créé pour le calendrier:', event);
      return event;
    });

    console.log('Tous les événements du calendrier:', events);
    this.calendarOptions.events = events;
  }

  // Formater l'heure au format HH:MM
  private formatTime(date: Date | string): string {
    if (!date) return '';
    
    const d = typeof date === 'string' ? new Date(date) : date;
    return d.getHours().toString().padStart(2, '0') + ':' + 
           d.getMinutes().toString().padStart(2, '0');
  }

  handleEventClick(info: any): void {
    console.log('Événement cliqué:', info);
    const sessionId = parseInt(info.event.id, 10);
    this.selectedSession = this.sessions.find(session => session.id === sessionId) || null;
    console.log('Session sélectionnée:', this.selectedSession);
  }

  closeDetails(): void {
    this.selectedSession = null;
  }

  registerToSession(sessionId: number): void {
    if (!this.selectedSession) return;
    
    // Vérifier si l'utilisateur est connecté
    if (!this.authService.currentAuthUserValue.isLogged()) {
      this.errorMessage = 'Vous devez être connecté pour vous inscrire à une séance.';
      return;
    }
    
    // Vérifier si l'ID utilisateur est disponible
    if (!localStorage.getItem('userId')) {
      this.errorMessage = 'Votre ID utilisateur n\'est pas disponible. Veuillez vous reconnecter.';
      // Tenter de rafraîchir les informations utilisateur
      this.refreshUserInfo();
      return;
    }
    
    this.isLoading = true;
    this.errorMessage = '';
    
    this.sessionService.registerToSession(sessionId).subscribe({
      next: () => {
        console.log('Inscription réussie à la séance:', sessionId);
        // Mettre à jour le nombre de participants
        if (this.selectedSession) {
          this.selectedSession.currentParticipants = (this.selectedSession.currentParticipants || 0) + 1;
          this.selectedSession.isUserRegistered = true;
        }
        // Mettre à jour la session dans la liste principale
        const sessionIndex = this.sessions.findIndex(s => s.id === sessionId);
        if (sessionIndex !== -1) {
          this.sessions[sessionIndex].isUserRegistered = true;
          this.sessions[sessionIndex].currentParticipants = (this.sessions[sessionIndex].currentParticipants || 0) + 1;
        }
        // Recharger toutes les séances pour avoir les données à jour
        this.filterSessions();
        this.isLoading = false;
      },
      error: (error) => {
        console.error('Erreur lors de l\'inscription à la séance:', error);
        // Afficher un message d'erreur plus précis
        if (error.message) {
          this.errorMessage = error.message;
        } else if (error.error && error.error.message) {
          this.errorMessage = error.error.message;
        } else {
          this.errorMessage = 'Impossible de s\'inscrire à la séance. Veuillez réessayer plus tard.';
        }
        this.isLoading = false;
      }
    });
  }

  unregisterFromSession(sessionId: number): void {
    if (!this.selectedSession) return;
    
    // Vérifier si l'utilisateur est connecté
    if (!this.authService.currentAuthUserValue.isLogged()) {
      this.errorMessage = 'Vous devez être connecté pour vous désinscrire d\'une séance.';
      return;
    }
    
    // Vérifier si l'ID utilisateur est disponible
    if (!localStorage.getItem('userId')) {
      this.errorMessage = 'Votre ID utilisateur n\'est pas disponible. Veuillez vous reconnecter.';
      // Tenter de rafraîchir les informations utilisateur
      this.refreshUserInfo();
      return;
    }
    
    this.isLoading = true;
    this.errorMessage = '';
    
    this.sessionService.unregisterFromSession(sessionId).subscribe({
      next: () => {
        console.log('Désinscription réussie de la séance:', sessionId);
        // Mettre à jour le nombre de participants
        if (this.selectedSession) {
          const session = this.selectedSession;
          if (typeof session.currentParticipants === 'number' && session.currentParticipants > 0) {
            session.currentParticipants -= 1;
          }
          session.isUserRegistered = false;
        }
        // Mettre à jour la session dans la liste principale
        const sessionIndex = this.sessions.findIndex(s => s.id === sessionId);
        if (sessionIndex !== -1) {
          this.sessions[sessionIndex].isUserRegistered = false;
          if (typeof this.sessions[sessionIndex].currentParticipants === 'number' && this.sessions[sessionIndex].currentParticipants > 0) {
            this.sessions[sessionIndex].currentParticipants -= 1;
          }
        }
        // Mettre à jour le filtrage si nécessaire
        this.filterSessions();
        this.isLoading = false;
      },
      error: (error) => {
        console.error('Erreur lors de la désinscription de la séance:', error);
        // Afficher un message d'erreur plus précis
        if (error.message) {
          this.errorMessage = error.message;
        } else if (error.error && error.error.message) {
          this.errorMessage = error.error.message;
        } else {
          this.errorMessage = 'Impossible de se désinscrire de la séance. Veuillez réessayer plus tard.';
        }
        this.isLoading = false;
      }
    });
  }

  // Fonction pour attribuer une couleur en fonction du type de séance
  private getColorByType(type: string): string {
    // Normaliser le type (minuscules, sans accents)
    const normalizedType = type.toLowerCase()
      .normalize("NFD")
      .replace(/[\u0300-\u036f]/g, "");
    
    // Attribuer une couleur en fonction du type
    switch (true) {
      case normalizedType.includes('yoga'):
        return '#4CAF50'; // Vert
      case normalizedType.includes('pilates'):
        return '#2196F3'; // Bleu
      case normalizedType.includes('crossfit'):
        return '#FF5722'; // Orange
      case normalizedType.includes('spinning') || normalizedType.includes('velo') || normalizedType.includes('cycle'):
        return '#9C27B0'; // Violet
      case normalizedType.includes('cardio'):
        return '#E91E63'; // Rose
      case normalizedType.includes('aqua') || normalizedType.includes('natation'):
        return '#00BCD4'; // Cyan
      case normalizedType.includes('stretching') || normalizedType.includes('etirement'):
        return '#FFC107'; // Jaune
      case normalizedType.includes('musculation') || normalizedType.includes('force') || normalizedType.includes('powerlifting'):
        return '#795548'; // Marron
      default:
        return this.getRandomColor();
    }
  }

  // Fonction utilitaire pour générer une couleur aléatoire
  private getRandomColor(): string {
    const colors = [
      '#4CAF50', // Vert
      '#2196F3', // Bleu
      '#FF5722', // Orange
      '#9C27B0', // Violet
      '#E91E63', // Rose
      '#00BCD4', // Cyan
      '#FFC107', // Jaune
      '#795548'  // Marron
    ];
    return colors[Math.floor(Math.random() * colors.length)];
  }
}
