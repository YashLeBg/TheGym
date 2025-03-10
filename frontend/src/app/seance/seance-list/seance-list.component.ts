import { Component } from '@angular/core';
import { Seance, SeanceService } from '../../services/seance.service';
import { AuthService } from '../../services/auth.service';
import { Router } from '@angular/router';

@Component({
  selector: 'app-seance-list',
  templateUrl: './seance-list.component.html',
  styleUrl: './seance-list.component.css',
})
export class SeanceListComponent {
  public seances: Seance[] = [];
  public ok: boolean = false;
  public okBDD: boolean = false;

  constructor(
    private seanceService: SeanceService,
    private authService: AuthService,
    private router: Router
  ) {}

  public ngOnInit(): void {
    this.seanceService.getSeances().subscribe(
      (data) => {
        this.seances = data;
        this.ok = true;
        this.okBDD = true;
      },
      (error) => {
        console.error('Erreur de chargement des séances', error);
        this.ok = true;
      }
    );
  }

  onSelectSeance(seance: Seance): void {
    this.seanceService.setCurrentSeance(seance);
  }
}
