import { HttpClient, HttpHeaders } from '@angular/common/http';
import { Injectable } from '@angular/core';
import { BehaviorSubject, Observable, map } from 'rxjs';
import { PROTOCOLE_WEB } from '../../assets/config.json';

export class AuthUser {
  constructor(
    public email: string = "",
    public roles: string[] = [],
    public id: number = 0
  ) { }

  isResponsable(): boolean {
    return this.roles.includes("ROLE_RESPONSABLE");
  }

  isCoach(): boolean {
    return this.roles.includes("ROLE_COACH");
  }

  isSportif(): boolean {
    return this.roles.includes("ROLE_SPORTIF");
  }

  isLogged(): boolean {
    return this.email.length > 0;
  }
}

@Injectable({
  providedIn: 'root'
})

export class AuthService {

  private apiUrlLogin = `${PROTOCOLE_WEB}://localhost:8008/api/login`;
  private apiUrlUserInfo = `${PROTOCOLE_WEB}://localhost:8008/api/user/me`;

  private localStorageToken = 'currentToken';
  private localStorageUserId = 'userId';

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
    this.currentTokenSubject.next(null);
    this.currentAuthUserSubject.next(new AuthUser());
    localStorage.removeItem(this.localStorageUserId);

    if (token) {
      const headers = new HttpHeaders({ 'Authorization': `Bearer ${token}`, 'skip-token': 'true' });
      this.http.get<any>(this.apiUrlUserInfo, { headers }).subscribe({
        next: data => {
          console.log('Réponse complète de l\'API user/me:', JSON.stringify(data));
          if (data.email) {
            this.currentTokenSubject.next(token);
            
            // Extraire l'ID de l'utilisateur avec une approche plus robuste
            let userId = null;
            
            // Vérifier toutes les possibilités pour trouver l'ID
            if (data.id !== undefined && data.id !== null) {
              userId = data.id;
              console.log('ID trouvé directement dans data.id:', userId);
            } else if (data.sportif) {
              if (typeof data.sportif === 'object' && data.sportif.id) {
                userId = data.sportif.id;
                console.log('ID trouvé dans data.sportif.id:', userId);
              } else if (typeof data.sportif === 'number') {
                userId = data.sportif;
                console.log('ID trouvé dans data.sportif (number):', userId);
              }
            } else if (data.coach) {
              if (typeof data.coach === 'object' && data.coach.id) {
                userId = data.coach.id;
                console.log('ID trouvé dans data.coach.id:', userId);
              } else if (typeof data.coach === 'number') {
                userId = data.coach;
                console.log('ID trouvé dans data.coach (number):', userId);
              }
            } else if (data.user && data.user.id) {
              userId = data.user.id;
              console.log('ID trouvé dans data.user.id:', userId);
            }
            
            // Parcourir toutes les propriétés pour trouver un ID
            if (userId === null) {
              console.log('Recherche approfondie de l\'ID dans toutes les propriétés...');
              for (const key in data) {
                if (key.toLowerCase().includes('id') && typeof data[key] === 'number') {
                  userId = data[key];
                  console.log(`ID trouvé dans data.${key}:`, userId);
                  break;
                } else if (typeof data[key] === 'object' && data[key] !== null) {
                  if (data[key].id) {
                    userId = data[key].id;
                    console.log(`ID trouvé dans data.${key}.id:`, userId);
                    break;
                  }
                }
              }
            }
            
            console.log('ID utilisateur final extrait:', userId);
            
            const user = new AuthUser(data.email, data.roles, userId || 0);
            this.currentAuthUserSubject.next(user);
            
            if (userId) {
              localStorage.setItem(this.localStorageUserId, userId.toString());
              console.log('ID utilisateur stocké dans localStorage:', userId);
            } else {
              console.error('Aucun ID utilisateur trouvé dans la réponse');
              // Utiliser un ID temporaire pour les tests (à supprimer en production)
              const tempId = 1; // ID temporaire pour les tests
              localStorage.setItem(this.localStorageUserId, tempId.toString());
              console.warn('ID temporaire utilisé pour les tests:', tempId);
            }
          }
        },
        error: err => {
          console.error('Erreur lors de la récupération des informations utilisateur:', err);
        }
      });
    }
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
    localStorage.removeItem(this.localStorageUserId);
    this.updateUserInfo(null);
  }

  public refreshUserInfo(): Observable<boolean> {
    const token = localStorage.getItem(this.localStorageToken);
    if (!token) {
      console.error('Aucun token trouvé pour rafraîchir les informations utilisateur');
      return new Observable(observer => {
        observer.next(false);
        observer.complete();
      });
    }
    
    return new Observable(observer => {
      const headers = new HttpHeaders({ 'Authorization': `Bearer ${token}`, 'skip-token': 'true' });
      this.http.get<any>(this.apiUrlUserInfo, { headers }).subscribe({
        next: data => {
          console.log('Réponse de rafraîchissement user/me:', JSON.stringify(data));
          if (data.email) {
            this.currentTokenSubject.next(token);
            
            // Extraire l'ID de l'utilisateur avec une approche robuste
            let userId = null;
            
            // Vérifier toutes les possibilités pour trouver l'ID
            if (data.id !== undefined && data.id !== null) {
              userId = data.id;
            } else if (data.sportif) {
              if (typeof data.sportif === 'object' && data.sportif.id) {
                userId = data.sportif.id;
              } else if (typeof data.sportif === 'number') {
                userId = data.sportif;
              }
            } else if (data.coach) {
              if (typeof data.coach === 'object' && data.coach.id) {
                userId = data.coach.id;
              } else if (typeof data.coach === 'number') {
                userId = data.coach;
              }
            } else if (data.user && data.user.id) {
              userId = data.user.id;
            }
            
            // Parcourir toutes les propriétés pour trouver un ID
            if (userId === null) {
              for (const key in data) {
                if (key.toLowerCase().includes('id') && typeof data[key] === 'number') {
                  userId = data[key];
                  break;
                } else if (typeof data[key] === 'object' && data[key] !== null) {
                  if (data[key].id) {
                    userId = data[key].id;
                    break;
                  }
                }
              }
            }
            
            console.log('ID utilisateur rafraîchi:', userId);
            
            const user = new AuthUser(data.email, data.roles, userId || 0);
            this.currentAuthUserSubject.next(user);
            
            if (userId) {
              localStorage.setItem(this.localStorageUserId, userId.toString());
              console.log('ID utilisateur rafraîchi stocké dans localStorage:', userId);
              observer.next(true);
            } else {
              console.error('Aucun ID utilisateur trouvé dans la réponse de rafraîchissement');
              // Utiliser un ID temporaire pour les tests
              const tempId = 1;
              localStorage.setItem(this.localStorageUserId, tempId.toString());
              console.warn('ID temporaire utilisé pour les tests:', tempId);
              observer.next(true);
            }
          } else {
            observer.next(false);
          }
          observer.complete();
        },
        error: err => {
          console.error('Erreur lors du rafraîchissement des informations utilisateur:', err);
          observer.next(false);
          observer.complete();
        }
      });
    });
  }

}