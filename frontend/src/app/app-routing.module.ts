import { NgModule } from '@angular/core';
import { RouterModule, Routes } from '@angular/router';
import { HomeComponent } from './home/home.component';
import { LoginComponent } from './login/login.component';
import { RegisterComponent } from './register/register.component';
import { SeanceListComponent } from './seance/seance-list/seance-list.component';
import { SeanceDetailComponent } from './seance/seance-detail/seance-detail.component';
import { CoachListComponent } from './coach/coach-list/coach-list.component';
import { CoachDetailComponent } from './coach/coach-detail/coach-detail.component';
import { ExerciceDetailComponent } from './exercice/exercice-detail/exercice-detail.component';

const routes: Routes = [
  { path: '', component: HomeComponent },
  { path: 'login', component: LoginComponent },
  { path: 'register', component: RegisterComponent },
  { path: 'seances', component: SeanceListComponent },
  { path: 'seances/:id', component: SeanceDetailComponent },
  { path: 'coachs', component: CoachListComponent },
  { path: 'coachs/:id', component: CoachDetailComponent },
  { path: 'exercices/:id', component: ExerciceDetailComponent },
];

@NgModule({
  imports: [RouterModule.forRoot(routes)],
  exports: [RouterModule],
})
export class AppRoutingModule {}
