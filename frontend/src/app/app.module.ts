import { NgModule } from '@angular/core';
import { BrowserModule } from '@angular/platform-browser';

import { LOCALE_ID } from '@angular/core';
import { registerLocaleData } from '@angular/common';
import localeFr from '@angular/common/locales/fr';

import { AppRoutingModule } from './app-routing.module';
import { AppComponent } from './app.component';
import { HTTP_INTERCEPTORS, HttpClientModule } from '@angular/common/http';
import { FormsModule } from '@angular/forms';
import { HomeComponent } from './home/home.component';
import { AuthInterceptor } from './services/auth.interceptor';
import { LoginComponent } from './login/login.component';
import { RegisterComponent } from './register/register.component';
import { SeanceDetailComponent } from './seance/seance-detail/seance-detail.component';
import { SeanceItemComponent } from './seance/seance-item/seance-item.component';
import { SeanceListComponent } from './seance/seance-list/seance-list.component';
import { SeanceEditComponent } from './seance/seance-edit/seance-edit.component';
import { CoachDetailComponent } from './coach/coach-detail/coach-detail.component';
import { ExerciceDetailComponent } from './exercice/exercice-detail/exercice-detail.component';

registerLocaleData(localeFr);

@NgModule({
  declarations: [
    AppComponent,
    HomeComponent,
    LoginComponent,
    RegisterComponent,
    SeanceDetailComponent,
    SeanceItemComponent,
    SeanceListComponent,
    SeanceEditComponent,
    CoachDetailComponent,
    ExerciceDetailComponent,
  ],
  imports: [BrowserModule, AppRoutingModule, HttpClientModule, FormsModule],
  providers: [
    { provide: LOCALE_ID, useValue: 'fr' },
    {
      provide: HTTP_INTERCEPTORS,
      useClass: AuthInterceptor,
      multi: true,
    },
  ],
  bootstrap: [AppComponent],
})
export class AppModule {}
