import { Component } from '@angular/core';
import { Router } from '@angular/router';
import { CalendarOptions } from '@fullcalendar/core/index.js';
import dayGridPlugin from '@fullcalendar/daygrid';
import timeGridPlugin from '@fullcalendar/timegrid';
import interactionPlugin from '@fullcalendar/interaction';

import { Seance } from '../../models/seance';
import { SeanceService } from '../../services/seance.service';
import { AuthService } from '../../services/auth.service';
import { User } from '../../models/user';
import { filter } from 'rxjs';

@Component({
  selector: 'app-planning',
  templateUrl: './planning.component.html',
  styleUrl: './planning.component.css'
})

export class PlanningComponent {
  public seances: Seance[] = [];
  public user?: User;

  public isLoading = false;
  public showOnlyMyRegistrations = false;

  public filteredSeances: Seance[] = [];
  public selectedSeance?: Seance;

  public errorMessage = '';

  calendarOptions: CalendarOptions = {
    plugins: [dayGridPlugin, timeGridPlugin, interactionPlugin],
    initialView: 'timeGridWeek',
    headerToolbar: {
      left: 'prev,today,next',
      center: 'title',
      right: 'dayGridMonth,timeGridWeek,timeGridDay'
    },
    events: [],
    eventClick: this.handleEventClick.bind(this),
    height: 'auto',
    allDaySlot: false,
    slotMinTime: '06:00:00',
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
    public authService: AuthService,
    private seanceService: SeanceService,
    private router: Router
  ) { }

  public ngOnInit(): void {
    this.authService.currentAuthUser
      .pipe(filter(user => user.id !== 0))
      .subscribe((user) => {
        if (!user.isLogged()) {
          this.router.navigate(['/login']);
          return;
        }

        this.user = this.authService.currentAuthUserValue;
        this.loadSeances();
      });
  }

  private getColorByType(type: string): string {
    switch (type) {
      case "bodybuilding":
        return '#795548'; // Marron
      case "crossfit":
        return '#FF5722'; // Orange
      case "powerlifting":
        return '#9C27B0'; // Violet
      case "streetlifting":
        return '#2196F3'; // Bleu
      case "yoga":
        return '#4CAF50'; // Vert
      case "cardio":
        return '#E91E63'; // Rose
      case "calisthenics":
        return '#00BCD4'; // Cyan
      default:
        return '#FFC107'; // Jaune
    }
  }

  private filtrerSeances(): void {
    if (this.showOnlyMyRegistrations) {
      // Filtrer pour n'afficher que les séances où l'utilisateur est inscrit
      this.filteredSeances = this.seances.filter((s) => {
        // Vérifier si l'utilisateur est inscrit à cette séance
        return s.sportifs.some(p => p.id === this.user!.id);
      });
    } else {
      // Afficher toutes les séances
      this.filteredSeances = [...this.seances];
    }

    this.updateCalendarEvents();
  }

  private updateCalendarEvents(): void {
    const events = this.filteredSeances.map(seance => {
      // Formater l'heure pour l'affichage
      const startTime = this.formatTime(seance.date_heure.toString());

      const startTimeFormat = this.formatTime(seance.date_heure.toString());

      // Ajouter une bordure spéciale pour les séances où l'utilisateur est inscrit
      const borderColor = this.isUserRegistered(seance) ? '#FFD700' : '#000000'; // Bordure dorée pour les inscriptions

      // Ajouter une classe CSS spéciale pour les séances où l'utilisateur est inscrit
      const classNames = this.isUserRegistered(seance) ? ['fc-event-registered'] : [];

      const colorSeance = this.getColorByType(seance.theme_seance);

      const event = {
        id: seance.id.toString(),
        title: seance.theme_seance,
        start: startTime,
        color: colorSeance,
        textColor: '#fff',
        opacity: 1,
        borderColor: borderColor,
        classNames: classNames,
        extendedProps: {
          description: `${seance.theme_seance} | ${seance.niveau_seance}`,
          coachName: `${seance.coach.prenom} | ${seance.coach.nom}`,
          currentParticipants: seance.sportifs.length,
          startTime: startTimeFormat,
          statut: seance.statut,
          isUserRegistered: this.isUserRegistered(seance)
        }
      };

      return event;
    });

    this.calendarOptions.events = events;
  }

  public loadSeances(): void {
    this.isLoading = true;
    this.errorMessage = '';

    this.seanceService.getSeances().subscribe({
      next: (seances) => {
        this.seances = seances;
        this.filtrerSeances();
        this.isLoading = false;
      },
      error: (error) => {
        console.error('Erreur lors du chargement des séances:', error);
        this.errorMessage = 'Impossible de charger les séances. Veuillez réessayer plus tard.';
        this.isLoading = false;
      }
    });
  }

  public toggleMyRegistrations(): void {
    this.showOnlyMyRegistrations = !this.showOnlyMyRegistrations;
    this.filtrerSeances();
  }

  private formatTime(date: string): Date {
    const d = new Date(date);

    return d;
  }

  public handleEventClick(info: any): void {
    const seanceId = parseInt(info.event.id, 10);
    this.selectedSeance = this.seances.find(s => s.id === seanceId) || undefined;
  }

  public closeDetails(): void {
    this.selectedSeance = undefined;
  }

  public registerToSeance(seanceId: number): void {
    if (!this.selectedSeance) return;

    this.isLoading = true;
    this.errorMessage = '';

    this.seanceService.registerSportifToSeance(seanceId, this.user!.id).subscribe({
      next: () => {
        this.loadSeances();
        this.closeDetails();
        this.isLoading = false;
      },
      error: (error) => {
        console.error('Erreur lors de l\'inscription à la séance:', error);
        this.errorMessage = 'Vous ne pouvez pas vous inscrire à cette séance.';
        this.isLoading = false;
      }
    });
  }

  public unregisterToSeance(seanceId: number): void {
    if (!this.selectedSeance) return;

    this.isLoading = true;
    this.errorMessage = '';

    this.seanceService.unregisterSportifToSeance(seanceId, this.user!.id).subscribe({
      next: () => {
        this.loadSeances();
        this.closeDetails();
        this.isLoading = false;
      },
      error: (error) => {
        console.error('Erreur lors de la désinscription de la séance:', error);
        this.errorMessage = 'Vous ne pouvez pas vous désinscrire de cette séance.';
        this.isLoading = false;
      }
    });
  }

  public getDateEnd(d: string): Date {
    const date = new Date(d);
    date.setHours(date.getHours() + 1);
    return date;
  }

  public isUserRegistered(seance: Seance): boolean {
    return seance.sportifs.some(sportif => sportif.id === this.user!.id);
  }
}