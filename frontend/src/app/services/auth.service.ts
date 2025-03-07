import { HttpClient, HttpHeaders } from '@angular/common/http';
import { Injectable } from '@angular/core';
import { BehaviorSubject, Observable, map } from 'rxjs';
import { PROTOCOLE_WEB } from '../../assets/config.json';

export class AuthUser {
  constructor(
    public email: string = "",
    public roles: string[] = []
  ) { }

  isLogged(): boolean {
    return this.email.length > 0 && this.roles.includes('ROLE_SPORTIF');
  }
}

@Injectable({
  providedIn: 'root'
})

export class AuthService {

  private apiUrlLogin = `${PROTOCOLE_WEB}://localhost:8008/api/login`;
  private apiUrlUserInfo = `${PROTOCOLE_WEB}://localhost:8008/api/user/me`;

  private localStorageToken = 'currentToken';

  private currentTokenSubject: BehaviorSubject<string | null>;
  public currentToken: Observable<string | null>;
  public get currentTokenValue(): string | null { return this.currentTokenSubject.value; }

  private currentAuthUserSubject: BehaviorSubject<AuthUser>;
  public currentAuthUser: Observable<AuthUser>;
  public get currentAuthUserValue(): AuthUser { return this.currentAuthUserSubject.value; }

  constructor(
    private http: HttpClient
  ) {
    this.currentTokenSubject = new BehaviorSubject<string | null>(null);
    this.currentToken = this.currentTokenSubject.asObservable();
    this.currentAuthUserSubject = new BehaviorSubject(new AuthUser());
    this.currentAuthUser = this.currentAuthUserSubject.asObservable();

    const storedToken: string | null = localStorage.getItem(this.localStorageToken);
    this.updateUserInfo(storedToken);
  }

  private updateUserInfo(token: string | null) {
    if (!token) {
      this.currentTokenSubject.next(null);
      this.currentAuthUserSubject.next(new AuthUser());
      localStorage.removeItem(this.localStorageToken);
      return;
    }

    const headers = new HttpHeaders({ 'Authorization': `Bearer ${token}`, 'skip-token': 'true' });
    this.http.get<AuthUser>(this.apiUrlUserInfo, { headers }).subscribe({
      next: data => {
        if (data.email) {
          this.currentTokenSubject.next(token);
          this.currentAuthUserSubject.next(new AuthUser(data.email, data.roles));
          localStorage.setItem(this.localStorageToken, token);
        }
      }
    });
  }

  public login(email: string, password: string): Observable<boolean> {
    return this.http.post<any>(this.apiUrlLogin, { email: email, password: password })
      .pipe(map(response => {
        if (response.token) {
          this.updateUserInfo(response.token);
          return true;
        } else {
          return false;
        }
      }));
  }

  public logout() {
    this.updateUserInfo(null);
    localStorage.removeItem(this.localStorageToken);
  }

}