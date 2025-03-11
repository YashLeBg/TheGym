import { HttpClient } from '@angular/common/http';
import { Injectable } from '@angular/core';
import { Observable } from 'rxjs';
import { Exercice } from '../models/exercice';

@Injectable({
  providedIn: 'root'
})

export class ExerciceService {
  private apiUrlExercices = 'https://localhost:8008/api/exercices';

  constructor(
    private http: HttpClient
  ) { }

  public getExercices(): Observable<Exercice[]> {
    return this.http.get<Exercice[]>(this.apiUrlExercices);
  }

  public getExercice(id: number): Observable<Exercice> {
    return this.http.get<Exercice>(`${this.apiUrlExercices}/${id}`);
  }
}