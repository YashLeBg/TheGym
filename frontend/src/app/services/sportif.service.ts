import { HttpClient } from '@angular/common/http';
import { Injectable } from '@angular/core';
import { Observable } from 'rxjs';
import { Sportif } from '../models/sportif';
import { PROTOCOLE_WEB } from '../../assets/config.json';

@Injectable({
  providedIn: 'root'
})

export class SportifService {
  private url = `${PROTOCOLE_WEB}://localhost:8008/api/sportifs`;

  constructor(
    private http: HttpClient
  ) { }

  createSportif(sportif: any): Observable<Sportif> {
    return this.http.post<Sportif>(this.url, sportif);
  }
}