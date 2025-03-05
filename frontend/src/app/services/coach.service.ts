import { HttpClient } from '@angular/common/http';
import { Injectable } from '@angular/core';
import { BehaviorSubject, Observable } from 'rxjs';

export class Coach {
  public specialities: string[] = [];
  public tarif_honoraire: number = 0;
  public seances: number[] = [];
  public fichesDePaie: number[] = [];
  public id: number = 0;
  public email: string = '';
  public roles: string[] = [];
  public nom: string = '';
  public prenom: string = '';
}

@Injectable({
  providedIn: 'root',
})
export class CoachService {
  private apiUrlCoachs = 'https://localhost:8008/api/coachs';

  private localStorageToken = 'currentToken';

  private currentSeanceSubject: BehaviorSubject<Coach | null>;
  public currentSeance: Observable<Coach | null>;

  constructor(private http: HttpClient) {
    this.currentSeanceSubject = new BehaviorSubject<Coach | null>(null);
    this.currentSeance = this.currentSeanceSubject.asObservable();
  }

  public getCoachs(): Observable<Coach[]> {
    return this.http.get<Coach[]>(this.apiUrlCoachs);
  }

  public getCoach(id: number): Observable<Coach> {
    return this.http.get<Coach>(`${this.apiUrlCoachs}/${id}`);
  }
}
