import { Component } from '@angular/core';
import { SportifService } from '../services/sportif.service';
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
    this.sportifService.createSportif(this.model).subscribe(
      (data) => {
        this.router.navigate(['/login']);
      },
      (error) => {
        console.log(error);
      }
    );
  }
}