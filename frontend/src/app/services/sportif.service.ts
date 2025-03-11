import { HttpClient } from '@angular/common/http';
import { Injectable } from '@angular/core';
import { Observable } from 'rxjs';
import { Sportif } from '../models/sportif';

@Injectable({
  providedIn: 'root',
})
export class SportifService {
  private apiUrlSportifs = `https://localhost:8008/api/sportifs`;

  constructor(
    private http: HttpClient
  ) { }

  public register(sportif: Sportif): Observable<Sportif> {
    return this.http.post<Sportif>(this.apiUrlSportifs, sportif);
  }
}