import { NgModule } from '@angular/core';
import { RouterModule, Routes } from '@angular/router';
import { HomeComponent } from './home/home.component';
import { LoginComponent } from './login/login.component';
import { RegisterComponent } from './register/register.component';
import { SeanceListComponent } from './seance/seance-list/seance-list.component';
import { SeanceDetailComponent } from './seance/seance-detail/seance-detail.component';
import { SeanceEditComponent } from './seance/seance-edit/seance-edit.component';

const routes: Routes = [
  { path: '', component: HomeComponent },
  { path: 'login', component: LoginComponent },
  { path: 'register', component: RegisterComponent },
  { path: 'seances', component: SeanceListComponent }, 
  { path: 'seances/new', component: SeanceEditComponent },
  { path: 'seances/edit/:id', component: SeanceEditComponent },
  { path: 'seances/:id', component: SeanceDetailComponent },
];

@NgModule({
  imports: [RouterModule.forRoot(routes)],
  exports: [RouterModule],
})
export class AppRoutingModule {}
