import { NgModule } from '@angular/core';
import { BrowserModule } from '@angular/platform-browser';
import { LOCALE_ID } from '@angular/core';
import { registerLocaleData } from '@angular/common';
import localeFr from '@angular/common/locales/fr';
import { HTTP_INTERCEPTORS, HttpClientModule } from '@angular/common/http';
import { FormsModule } from '@angular/forms';
import { FullCalendarModule } from '@fullcalendar/angular';

import { AppRoutingModule } from './app-routing.module';
import { AuthInterceptor } from '../services/auth.interceptor';

import { AppComponent } from './app.component';
import { HomeComponent } from './home/home.component';
import { LoginComponent } from './login/login.component';
import { RegisterComponent } from './register/register.component';
import { CoachListComponent } from './coach/coach-list/coach-list.component';
import { CoachItemComponent } from './coach/coach-item/coach-item.component';
import { CoachDetailComponent } from './coach/coach-detail/coach-detail.component';
import { TypeListComponent } from './seance/type-list/type-list.component';
import { TypeItemComponent } from './seance/type-item/type-item.component';
import { SeanceItemComponent } from './seance/seance-item/seance-item.component';
import { SeanceListComponent } from './seance/seance-list/seance-list.component';
import { PlanningComponent } from './planning/planning.component';

registerLocaleData(localeFr);

@NgModule({
  declarations: [
    AppComponent,
    HomeComponent,
    LoginComponent,
    RegisterComponent,
    CoachListComponent,
    CoachItemComponent,
    CoachDetailComponent,
    TypeListComponent,
    TypeItemComponent,
    SeanceItemComponent,
    SeanceListComponent,
    PlanningComponent
  ],
  imports: [
    BrowserModule,
    AppRoutingModule,
    HttpClientModule,
    FormsModule,
    FullCalendarModule
  ],
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

export class AppModule { }