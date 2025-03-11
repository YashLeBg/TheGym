import { Injectable } from '@angular/core';
import { HttpClient } from '@angular/common/http';
import { Observable, map, of } from 'rxjs';
import { Coach } from '../models/coach';

@Injectable({
  providedIn: 'root'
})
export class CoachService {
  private apiUrl = 'http://localhost:8008/api';
  private coachs: Coach[] = [];
  private loaded = false;

  constructor(private http: HttpClient) { }

  // Récupérer tous les coachs
  getAllCoachs(): Observable<Coach[]> {
    // Si les coachs sont déjà chargés, retourner la version en cache
    if (this.loaded && this.coachs.length > 0) {
      return of(this.coachs);
    }

    // Sinon, charger depuis l'API
    return this.http.get<Coach[]>(`${this.apiUrl}/coachs`).pipe(
      map(coachs => {
        this.coachs = coachs;
        this.loaded = true;
        return coachs;
      })
    );
  }

  // Récupérer un coach par son ID
  getCoachById(id: number): Observable<Coach | undefined> {
    // Si les coachs sont déjà chargés, chercher dans le cache
    if (this.loaded && this.coachs.length > 0) {
      const coach = this.coachs.find(c => c.id === id);
      return of(coach);
    }

    // Sinon, charger tous les coachs puis filtrer
    return this.getAllCoachs().pipe(
      map(coachs => coachs.find(c => c.id === id))
    );
  }

  // Formater le nom complet d'un coach
  getCoachFullName(coach: Coach | undefined): string {
    if (!coach) return 'Coach inconnu';
    return `${coach.prenom} ${coach.nom}`;
  }

  // Récupérer le nom complet d'un coach par son ID
  getCoachNameById(id: number): Observable<string> {
    return this.getCoachById(id).pipe(
      map(coach => this.getCoachFullName(coach))
    );
  }
}