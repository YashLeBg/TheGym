import { HttpEvent, HttpHandler, HttpInterceptor, HttpInterceptorFn, HttpRequest } from '@angular/common/http';
import { Injectable } from '@angular/core';
import { AuthService } from './auth.service';
import { Observable } from 'rxjs';

@Injectable()
export class AuthInterceptor implements HttpInterceptor {
  constructor(
    private authService: AuthService
  ) { }

  intercept(req: HttpRequest<any>, next: HttpHandler): Observable<HttpEvent<any>> {
    if (!req.headers.has('skip-token')) {
      let currentToken = this.authService.currentTokenValue;
      if (currentToken) {
        req = req.clone({
          setHeaders: {
            Authorization: `Bearer ${currentToken}`
          }
        });
      }
    } else {
      req = req.clone({
        headers: req.headers.delete('skip-token')
      });
    }

    return next.handle(req);
  }
}