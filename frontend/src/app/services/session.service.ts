import { Injectable } from '@angular/core';
import { HttpClient, HttpHeaders } from '@angular/common/http';
import { Observable, map, forkJoin, of } from 'rxjs';
import { Session } from '../models/session';
import { environment } from '../../environments/environment';
import { CoachService } from './coach.service';

@Injectable({
  providedIn: 'root'
})
export class SessionService {
  // Mise à jour du port de l'API à 8008
  private apiUrl = 'http://localhost:8008/api';

  constructor(
    private http: HttpClient,
    private coachService: CoachService
  ) { }

  // Get all sessions
  getSessions(): Observable<Session[]> {
    return this.http.get<any[]>(`${this.apiUrl}/seances`).pipe(
      map(data => {
        console.log('Données brutes de l\'API:', data);
        return this.mapApiDataToSessions(data);
      })
    );
  }

  // Get sessions for a specific week
  getSessionsByWeek(startDate: Date, endDate: Date): Observable<Session[]> {
    return this.http.get<any[]>(`${this.apiUrl}/seances`, {
      params: {
        startDate: startDate.toISOString(),
        endDate: endDate.toISOString()
      }
    }).pipe(
      map(data => this.mapApiDataToSessions(data))
    );
  }

  // S'inscrire à une séance
  registerToSession(sessionId: number): Observable<any> {
    // Récupérer l'ID du sportif depuis le localStorage
    const sportifId = localStorage.getItem('userId');
    console.log('ID sportif récupéré du localStorage pour inscription:', sportifId);
    
    if (!sportifId) {
      console.error('ID du sportif non trouvé dans le localStorage');
      return new Observable(observer => {
        observer.error({ message: "L'ID du sportif est requis. Veuillez vous reconnecter." });
      });
    }
    
    // Convertir en nombre et vérifier que c'est un nombre valide
    const sportifIdNum = parseInt(sportifId, 10);
    if (isNaN(sportifIdNum)) {
      console.error('ID du sportif invalide:', sportifId);
      return new Observable(observer => {
        observer.error({ message: "L'ID du sportif est invalide. Veuillez vous reconnecter." });
      });
    }
    
    // URL de l'API pour l'inscription
    const url = `${this.apiUrl}/seances/${sessionId}/inscription`;
    console.log('URL d\'inscription:', url);
    
    // Définir les en-têtes pour la requête
    const headers = new HttpHeaders({
      'Content-Type': 'application/json'
    });
    
    // Corps de la requête
    const body = { sportif_id: sportifIdNum };
    console.log('Corps de la requête d\'inscription:', body);
    
    return this.http.post(url, body, { headers });
  }

  // Se désinscrire d'une séance
  unregisterFromSession(sessionId: number): Observable<any> {
    // Récupérer l'ID du sportif depuis le localStorage
    const sportifId = localStorage.getItem('userId');
    console.log('ID sportif récupéré du localStorage pour désinscription:', sportifId);
    
    if (!sportifId) {
      console.error('ID du sportif non trouvé dans le localStorage');
      return new Observable(observer => {
        observer.error({ message: "L'ID du sportif est requis. Veuillez vous reconnecter." });
      });
    }
    
    // Convertir en nombre et vérifier que c'est un nombre valide
    const sportifIdNum = parseInt(sportifId, 10);
    if (isNaN(sportifIdNum)) {
      console.error('ID du sportif invalide:', sportifId);
      return new Observable(observer => {
        observer.error({ message: "L'ID du sportif est invalide. Veuillez vous reconnecter." });
      });
    }
    
    // URL de l'API pour la désinscription
    // Essayons une URL différente pour la désinscription
    const url = `${this.apiUrl}/seances/${sessionId}/desinscription`;
    console.log('URL de désinscription:', url);
    
    // Définir les en-têtes pour la requête
    const headers = new HttpHeaders({
      'Content-Type': 'application/json'
    });
    
    // Corps de la requête
    const body = { sportif_id: sportifIdNum };
    console.log('Corps de la requête de désinscription:', body);
    
    // Essayons d'abord avec une requête POST pour la désinscription
    return this.http.post(url, body, { headers });
  }

  // Mapper les données de l'API au modèle Session
  private mapApiDataToSessions(data: any[]): Session[] {
    if (!data || !Array.isArray(data)) {
      console.error('Les données reçues ne sont pas un tableau:', data);
      return [];
    }

    // Récupérer l'ID de l'utilisateur connecté
    const userId = localStorage.getItem('userId') ? parseInt(localStorage.getItem('userId') || '0', 10) : 0;

    return data.map(item => {
      console.log('Traitement de l\'élément API:', item);
      
      // Utiliser date_heure pour la date de début
      let startDate: Date;
      let endDate: Date;
      
      if (item.date_heure) {
        console.log('Utilisation du champ date_heure:', item.date_heure);
        startDate = new Date(item.date_heure);
        
        // Calculer la date de fin (par défaut, ajouter 1 heure si durée non spécifiée)
        const durationMinutes = item.duree || 60; // Durée par défaut de 60 minutes
        endDate = new Date(startDate.getTime() + durationMinutes * 60000);
      } else {
        // Fallback sur les autres champs possibles
        startDate = item.start ? new Date(item.start) : 
                   item.debut ? new Date(item.debut) : new Date();
        
        endDate = item.end ? new Date(item.end) : 
                 item.fin ? new Date(item.fin) : new Date(startDate.getTime() + 60 * 60000);
      }
      
      // Priorité au champ "theme_seance" pour le titre, puis "type", etc.
      const title = this.capitalizeFirstLetter(
        item.theme_seance || 
        item.type || 
        item.type_seance || 
        item.title || 
        item.nom || 
        'Séance'
      );
      
      // Extraire le thème de la séance pour la couleur
      const theme = item.theme_seance || 
                   item.type || 
                   item.type_seance || 
                   title;
      
      // Récupérer les informations du coach
      let coachName = '';
      let coachId = 0;
      
      if (item.coach) {
        if (typeof item.coach === 'object') {
          // Si coach est un objet, utiliser ses propriétés nom et prenom
          coachId = item.coach.id || 0;
          const nom = item.coach.nom || '';
          const prenom = item.coach.prenom || '';
          
          if (nom && prenom) {
            coachName = `${prenom} ${nom}`;
          } else if (nom) {
            coachName = nom;
          } else if (prenom) {
            coachName = prenom;
          } else {
            coachName = `Coach #${coachId}`;
          }
        } else if (typeof item.coach === 'number') {
          // Si coach est un ID, on le stocke pour le résoudre plus tard
          coachId = item.coach;
          coachName = `Coach #${coachId}`;
        } else {
          // Si coach est une chaîne, l'utiliser directement
          coachName = item.coach;
        }
      } else if (item.coachName) {
        coachName = item.coachName;
      } else if (item.coach_id) {
        // Si on a seulement l'ID du coach, le stocker pour le résoudre plus tard
        coachId = item.coach_id;
        coachName = `Coach #${coachId}`;
      }
      
      // Récupérer le niveau de la séance
      const niveau = item.niveau_seance || '';
      
      // Créer la description en incluant le type et le niveau si disponibles
      let description = item.description || '';
      
      if (item.type_seance && !description.includes(item.type_seance)) {
        description = `Type: ${item.type_seance}${description ? ' - ' + description : ''}`;
      }
      
      if (niveau && !description.includes(niveau)) {
        description = `${description ? description + ' - ' : ''}Niveau: ${niveau}`;
      }
      
      // Calculer le nombre de participants
      const currentParticipants = item.currentParticipants || 
                                 item.participants || 
                                 item.nb_participants || 
                                 (item.sportifs ? item.sportifs.length : 0);
      
      // Vérifier si l'utilisateur est inscrit à cette séance
      let isUserRegistered = false;
      
      // Vérifier dans la liste des sportifs
      if (item.sportifs && Array.isArray(item.sportifs)) {
        isUserRegistered = item.sportifs.some((sportif: any) => {
          if (typeof sportif === 'object' && sportif !== null) {
            return sportif.id === userId;
          } else if (typeof sportif === 'number') {
            return sportif === userId;
          }
          return false;
        });
      }
      
      // Créer un objet Session avec les champs mappés
      const session: Session = {
        id: item.id || 0,
        title: title,
        start: startDate,
        end: endDate,
        description: description,
        coachName: coachName,
        coachId: coachId,
        currentParticipants: currentParticipants,
        color: item.color || this.getColorByTheme(theme),
        statut: item.statut || 'prevue',
        isUserRegistered: isUserRegistered,
        sportifs: item.sportifs
      };

      console.log('Session mappée:', session);
      return session;
    });
  }

  // Enrichir les sessions avec les noms complets des coachs
  enrichSessionsWithCoachNames(sessions: Session[]): Observable<Session[]> {
    if (!sessions || sessions.length === 0) {
      return of([]);
    }

    // Filtrer les sessions qui ont un ID de coach mais pas de nom complet
    const sessionsToEnrich = sessions.filter(
      session => session.coachId && (!session.coachName || session.coachName.includes('#'))
    );

    if (sessionsToEnrich.length === 0) {
      return of(sessions);
    }

    // Récupérer tous les coachs en une seule requête
    return this.coachService.getAllCoachs().pipe(
      map(coachs => {
        // Mettre à jour les sessions avec les noms des coachs
        return sessions.map(session => {
          if (session.coachId) {
            const coach = coachs.find(c => c.id === session.coachId);
            if (coach) {
              session.coachName = `${coach.prenom} ${coach.nom}`;
            }
          }
          return session;
        });
      })
    );
  }

  // Fonction pour mettre en majuscule la première lettre
  private capitalizeFirstLetter(text: string): string {
    if (!text) return '';
    return text.charAt(0).toUpperCase() + text.slice(1).toLowerCase();
  }

  // Fonction pour attribuer une couleur en fonction du thème de la séance
  private getColorByTheme(theme: string): string {
    // Normaliser le thème (minuscules, sans accents)
    const normalizedTheme = theme.toLowerCase()
      .normalize("NFD")
      .replace(/[\u0300-\u036f]/g, "");
    
    // Palette de couleurs par thème
    const themeColors = {
      // Yoga et relaxation
      'yoga': '#4CAF50',                // Vert
      'meditation': '#81C784',          // Vert clair
      'relaxation': '#A5D6A7',          // Vert très clair
      
      // Pilates et étirements
      'pilates': '#2196F3',             // Bleu
      'stretching': '#FFC107',          // Jaune
      'etirement': '#FFC107',           // Jaune
      'souplesse': '#FFD54F',           // Jaune clair
      
      // Cardio et haute intensité
      'cardio': '#E91E63',              // Rose
      'hiit': '#F44336',                // Rouge
      'tabata': '#EF5350',              // Rouge clair
      'crossfit': '#FF5722',            // Orange
      'circuit': '#FF7043',             // Orange clair
      
      // Cyclisme
      'spinning': '#9C27B0',            // Violet
      'velo': '#9C27B0',                // Violet
      'cycle': '#9C27B0',               // Violet
      'rpm': '#BA68C8',                 // Violet clair
      
      // Aquatique
      'aqua': '#00BCD4',                // Cyan
      'natation': '#00BCD4',            // Cyan
      'piscine': '#4DD0E1',             // Cyan clair
      
      // Musculation
      'musculation': '#795548',         // Marron
      'force': '#795548',               // Marron
      'powerlifting': '#8D6E63',        // Marron clair
      'halteres': '#A1887F',            // Marron très clair
      
      // Danse
      'danse': '#FF9800',               // Orange foncé
      'zumba': '#FFA726',               // Orange moyen
      'salsa': '#FFB74D',               // Orange clair
      
      // Arts martiaux
      'boxe': '#607D8B',                // Bleu-gris
      'karate': '#78909C',              // Bleu-gris clair
      'judo': '#90A4AE',                // Bleu-gris très clair
      'taekwondo': '#B0BEC5',           // Bleu-gris pâle
      
      // Bien-être
      'bien-etre': '#009688',           // Turquoise
      'sante': '#26A69A',               // Turquoise clair
      'detente': '#4DB6AC',             // Turquoise très clair
    };
    
    // Chercher si le thème contient un des mots-clés
    for (const [keyword, color] of Object.entries(themeColors)) {
      if (normalizedTheme.includes(keyword)) {
        return color;
      }
    }
    
    // Si aucun thème correspondant n'est trouvé, utiliser une couleur aléatoire
    return this.getRandomColor();
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