import { HttpClient, HttpHeaders } from '@angular/common/http';
import { Injectable } from '@angular/core';
import { BehaviorSubject, Observable, map } from 'rxjs';
import { User } from '../models/user';

@Injectable({
  providedIn: 'root'
})

export class AuthService {

  private apiUrlLogin = `https://localhost:8008/api/login`;
  private apiUrlUserInfo = `https://localhost:8008/api/user/me`;

  private localStorageToken = 'currentToken';

  private currentTokenSubject: BehaviorSubject<string | null>;
  public currentToken: Observable<string | null>;
  public get currentTokenValue(): string | null { return this.currentTokenSubject.value; }

  private currentAuthUserSubject: BehaviorSubject<User>;
  public currentAuthUser: Observable<User>;
  public get currentAuthUserValue(): User { return this.currentAuthUserSubject.value; }

  constructor(
    private http: HttpClient
  ) {
    this.currentTokenSubject = new BehaviorSubject<string | null>(null);
    this.currentToken = this.currentTokenSubject.asObservable();
    this.currentAuthUserSubject = new BehaviorSubject(new User(0, '', '', '', []));
    this.currentAuthUser = this.currentAuthUserSubject.asObservable();

    const storedToken: string | null = localStorage.getItem(this.localStorageToken);
    this.updateUserInfo(storedToken);
  }

  private updateUserInfo(token: string | null) {
    if (!token) {
      this.currentTokenSubject.next(null);
      this.currentAuthUserSubject.next(new User(0, '', '', '', []));
      localStorage.removeItem(this.localStorageToken);
      return;
    }

    const headers = new HttpHeaders({ 'Authorization': `Bearer ${token}`, 'skip-token': 'true' });
    this.http.get<User>(this.apiUrlUserInfo, { headers }).subscribe({
      next: data => {
        if (data.email) {
          this.currentTokenSubject.next(token);
          this.currentAuthUserSubject.next(
            new User(
              data.id,
              data.nom,
              data.prenom,
              data.email,
              data.roles
            )
          );
          localStorage.setItem(this.localStorageToken, token);
        }
      }
    });
  }

  public login(email: string, password: string): Observable<boolean> {
    return this.http.post<any>(this.apiUrlLogin, { email: email, password: password })
      .pipe(map(response => {
        if (response.token) {
          localStorage.setItem(this.localStorageToken, response.token);
          this.updateUserInfo(response.token);
          return true;
        } else {
          return false;
        }
      }));
  }

  public logout() {
    localStorage.removeItem(this.localStorageToken);
    this.updateUserInfo(null);
  }
}