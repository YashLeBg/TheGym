import { Component } from '@angular/core';
import { AuthService } from '../services/auth.service';
import { Router } from '@angular/router';

@Component({
  selector: 'app-login',
  templateUrl: './login.component.html',
  styleUrl: './login.component.css'
})

export class LoginComponent {
  model: any = {};

  constructor(
    private autService: AuthService,
    private router: Router
  ) { }

  onSubmit() {
    this.autService.login(this.model.email, this.model.password).subscribe({
      next: () => this.router.navigate(['/']),
      error: error => {
        this.model.error = true;
        console.error("Error while logging in: ", error)
      }
    });
  }
}