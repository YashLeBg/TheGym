import { Injectable } from '@angular/core';
import { HttpClient, HttpHeaders } from '@angular/common/http';
import { BehaviorSubject, Observable, map } from 'rxjs';
import { Coach } from './coach.service';
import { Sportif } from './sportif.service';
import { Exercice } from './exercice.service';

export class Seance {
  constructor(
    public id: number = 0,
    public coach: Coach = new Coach(),
    public date_heure: Date = new Date(),
    public type_seance: string = '',
    public theme_seance: string = '',
    public statut: string = '',
    public niveau_seance: string = '',
    public sportifs: Sportif[] = [],
    public exercices: Exercice[] = []
  ) {}
}

@Injectable({
  providedIn: 'root',
})
export class SeanceService {
  private apiUrlSeances = 'https://localhost:8008/api/seances';

  private currentSeanceSubject: BehaviorSubject<Seance | null>;
  public currentSeance: Observable<Seance | null>;

  constructor(private http: HttpClient) {
    this.currentSeanceSubject = new BehaviorSubject<Seance | null>(null);
    this.currentSeance = this.currentSeanceSubject.asObservable();
  }

  public getSeances(): Observable<Seance[]> {
    return this.http.get<Seance[]>(this.apiUrlSeances);
  }

  public getSeance(id: number): Observable<Seance> {
    return this.http.get<Seance>(`${this.apiUrlSeances}/${id}`);
  }

  public setCurrentSeance(seance: Seance | null): void {
    this.currentSeanceSubject.next(seance);
  }
}
