import { HttpClient } from '@angular/common/http';
import { Injectable } from '@angular/core';
import { Observable } from 'rxjs';
import { Coach } from '../models/coach';

@Injectable({
  providedIn: 'root'
})

export class CoachService {
  private apiUrl = 'https://localhost:8008/api/coachs';

  constructor(
    private http: HttpClient
  ) { }

  public getCoachs(): Observable<Coach[]> {
    return this.http.get<Coach[]>(this.apiUrl);
  }

  public getCoach(id: number): Observable<Coach> {
    return this.http.get<Coach>(`${this.apiUrl}/${id}`);
  }
}