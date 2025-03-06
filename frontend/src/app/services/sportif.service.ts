import { HttpClient } from '@angular/common/http';
import { Injectable } from '@angular/core';
import { BehaviorSubject, Observable } from 'rxjs';
import { Seance } from './seance.service';

export class Sportif {
  constructor(
    public date_inscription: Date = new Date(),
    public niveau_sportif: string = '',
    public seances: Seance[] = [],
    public id: number = 0,
    public email: string = '',
    public roles: string[] = [],
    public nom: string = '',
    public prenom: string = ''
  ) {}
}

@Injectable({
  providedIn: 'root',
})
export class SportifService {
  private apiUrlSportifs = 'https://localhost:8008/api/sportifs';

  private localStorageToken = 'currentToken';

  private currentSeanceSubject: BehaviorSubject<Sportif | null>;
  public currentSeance: Observable<Sportif | null>;

  constructor(private http: HttpClient) {
    this.currentSeanceSubject = new BehaviorSubject<Sportif | null>(null);
    this.currentSeance = this.currentSeanceSubject.asObservable();
  }

  public getSportif(id: number): Observable<Sportif> {
    return this.http.get<Sportif>(`${this.apiUrlSportifs}/${id}`);
  }

  public getSportifsByIds(ids: number[]): Observable<Sportif[]> {
    return this.http.get<Sportif[]>(
      `${this.apiUrlSportifs}?ids=${ids.join(',')}`
    );
  }

  public createSportif(sportif: any): Observable<Sportif> {
    return this.http.post<Sportif>(this.apiUrlSportifs, sportif);
  }
}
