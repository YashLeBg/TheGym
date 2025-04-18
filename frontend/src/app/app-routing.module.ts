import { NgModule } from '@angular/core';
import { RouterModule, Routes } from '@angular/router';

import { HomeComponent } from './home/home.component';
import { LoginComponent } from './login/login.component';
import { RegisterComponent } from './register/register.component';
import { CoachListComponent } from './coach/coach-list/coach-list.component';
import { CoachDetailComponent } from './coach/coach-detail/coach-detail.component';
import { TypeListComponent } from './seance/type-list/type-list.component';
import { SeanceListComponent } from './seance/seance-list/seance-list.component';
import { PlanningComponent } from './planning/planning.component';
import { SeanceDetailComponent } from './seance/seance-detail/seance-detail.component';
import { ExerciceDetailComponent } from './exercice/exercice-detail/exercice-detail.component';
import { BilanComponent } from './bilan/bilan.component';

const routes: Routes = [
  { path: '', component: HomeComponent },
  { path: 'login', component: LoginComponent },
  { path: 'register', component: RegisterComponent },
  { path: 'coachs', component: CoachListComponent },
  { path: 'coachs/:id', component: CoachDetailComponent },
  { path: 'types', component: TypeListComponent },
  { path: 'seances', component: SeanceListComponent },
  { path: 'seances/:id', component: SeanceDetailComponent },
  { path: 'planning', component: PlanningComponent },
  { path: 'exercices/:id', component: ExerciceDetailComponent },
  { path: 'bilan', component: BilanComponent }
];

@NgModule({
  imports: [RouterModule.forRoot(routes)],
  exports: [RouterModule],
})

export class AppRoutingModule { }