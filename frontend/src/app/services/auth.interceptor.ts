import { HttpEvent, HttpHandler, HttpInterceptor, HttpRequest } from '@angular/common/http';
import { Injectable } from '@angular/core';
import { Observable } from 'rxjs';

@Injectable()
export class AuthInterceptor implements HttpInterceptor {
  private localStorageToken = 'currentToken';

  intercept(req: HttpRequest<any>, next: HttpHandler): Observable<HttpEvent<any>> {
    if (!req.headers.has('skip-token')) {
      const currentToken = localStorage.getItem(this.localStorageToken);
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