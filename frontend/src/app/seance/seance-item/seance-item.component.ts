import { Component, Input } from '@angular/core';
import { Seance } from '../../../models/seance';
import { Exercice } from '../../../models/exercice';
import { Coach } from '../../../models/coach';
import { AuthService } from '../../../services/auth.service';
import { filter } from 'rxjs';

@Component({
  selector: 'app-seance-item',
  templateUrl: './seance-item.component.html',
  styleUrl: './seance-item.component.css'
})

export class SeanceItemComponent {
  @Input() seance!: Seance;
  public exercices: Exercice[] = [];
  public coach: Coach = new Coach(0, '', '', '', [], 0, []);

  constructor(
    public authService: AuthService
  ) { }

  public ngOnInit(): void {
    this.authService.currentAuthUser.pipe(filter(user => user.id !== 0)).subscribe((user) => {
      if (user.isLogged()) {
        this.seance.exercices.forEach((exercice) => {
          this.exercices.push(
            new Exercice(
              exercice.id,
              exercice.nom,
              exercice.description,
              0,
              "",
              []
            )
          );
        });
      }
    });

    this.coach = new Coach(
      this.seance.coach.id,
      "",
      this.seance.coach.nom,
      this.seance.coach.prenom,
      this.coach.specialites,
      this.coach.tarif_horaire,
      []
    );
  }

  public getDate(d: string): string {
    const date = new Date(d);
    return date.toLocaleDateString('fr-FR', {
      day: 'numeric',
      month: 'long',
      year: 'numeric',
    }) + ", " + date.toLocaleTimeString('fr-FR');
  }
}