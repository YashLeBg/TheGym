import { Component } from '@angular/core';
import { Seance } from '../../../models/seance';
import { SeanceService } from '../../../services/seance.service';

@Component({
  selector: 'app-seance-list',
  templateUrl: './seance-list.component.html',
  styleUrl: './seance-list.component.css'
})

export class SeanceListComponent {
  public seances: Seance[] = [];
  public ok: boolean = false;
  public okBDD: boolean = false;

  constructor(
    private seanceService: SeanceService
  ) { }

  public ngOnInit(): void {
    this.seanceService.getSeances().subscribe({
      next: (seances) => {
        this.seances = seances.filter(seance => new Date(seance.date_heure) > new Date());
        this.seances.sort((a, b) => new Date(a.date_heure).getTime() - new Date(b.date_heure).getTime());

        this.ok = true;
        this.okBDD = true;
      },
      error: (error) => {
        this.ok = true;
        console.error('Erreur lors du chargement des séances :', error);
      }
    });
  }
}