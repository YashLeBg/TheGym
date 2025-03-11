import { Component } from '@angular/core';
import { SportifService } from '../../services/sportif.service';
import { Router } from '@angular/router';

@Component({
  selector: 'app-register',
  templateUrl: './register.component.html',
  styleUrl: './register.component.css'
})

export class RegisterComponent {
  model: any = {};

  constructor(
    private sportifService: SportifService,
    private router: Router
  ) { }

  onSubmit() {
    this.sportifService.register(this.model).subscribe({
      next: () => this.router.navigate(['/login']),
      error: error => {
        this.model.error = true;
        console.error("Error while registering: ", error)
      }
    });
  }
}