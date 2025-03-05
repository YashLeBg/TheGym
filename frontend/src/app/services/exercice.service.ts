import { Injectable } from '@angular/core';
import { HttpClient } from '@angular/common/http';
import { Observable } from 'rxjs';

export class Exercice {
  public id: number = 0;
  public nom: string = '';
  public description: string = '';
  public duree_estimee: number = 0;
  public diffuculte: string = '';
}

@Injectable({
  providedIn: 'root',
})
export class ExerciceService {
  private apiUrlExercices = 'https://localhost:8008/api/exercices';

  constructor(private http: HttpClient) {}

  getExercice(id: number): Observable<Exercice> {
    return this.http.get<Exercice>(`${this.apiUrlExercices}/${id}`);
  }

  getExercicesByIds(ids: number[]): Observable<Exercice[]> {
    return this.http.get<Exercice[]>(
      `${this.apiUrlExercices}?ids=${ids.join(',')}`
    );
  }
}
