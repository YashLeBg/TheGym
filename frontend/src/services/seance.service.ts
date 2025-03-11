import { HttpClient } from '@angular/common/http';
import { Injectable } from '@angular/core';
import { Observable } from 'rxjs';
import { Seance } from '../models/seance';

@Injectable({
  providedIn: 'root'
})

export class SeanceService {
  private apiUrlSeances = 'https://localhost:8008/api/seances';

  constructor(
    private http: HttpClient
  ) { }

  public getSeances(): Observable<Seance[]> {
    return this.http.get<Seance[]>(this.apiUrlSeances);
  }

  public getSeance(id: number): Observable<Seance> {
    return this.http.get<Seance>(`${this.apiUrlSeances}/${id}`);
  }
}