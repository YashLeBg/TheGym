import { Component } from '@angular/core';
import { Seance, SeanceService } from '../../services/seance.service';

@Component({
  selector: 'app-seance-list',
  templateUrl: './seance-list.component.html',
  styleUrl: './seance-list.component.css',
})
export class SeanceListComponent {
  seances: Seance[] = [];
  ok: boolean = false;
  okBDD: boolean = false;

  constructor(private seanceService: SeanceService) {}

  ngOnInit(): void {
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
