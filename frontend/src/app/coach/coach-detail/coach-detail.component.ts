import { Component } from '@angular/core';
import { Coach } from '../../../models/coach';
import { CoachService } from '../../../services/coach.service';
import { ActivatedRoute, Router } from '@angular/router';
import { Seance } from '../../../models/seance';

@Component({
  selector: 'app-coach-detail',
  templateUrl: './coach-detail.component.html',
  styleUrl: './coach-detail.component.css'
})

export class CoachDetailComponent {
  public coach: Coach = new Coach(0, '', '', '', [], 0, []);
  public seances: Seance[] = [];
  public ok: boolean = false;

  constructor(
    private coachService: CoachService,
    private router: Router,
    private route: ActivatedRoute
  ) { }

  public ngOnInit(): void {
    const id: number = this.route.snapshot.params['id'];

    this.coachService.getCoach(id).subscribe({
      next: (coach) => {
        this.coach = coach;
        coach.seances.forEach((seance) => {
          this.seances.push(
            new Seance(
              seance.id,
              seance.date_heure,
              seance.type_seance,
              seance.theme_seance,
              seance.statut,
              seance.niveau_seance,
              {
                id: coach.id,
                nom: coach.nom,
                prenom: coach.prenom,
                tarif_horaire: coach.tarif_horaire,
                specialites: coach.specialites
              },
              [],
              []
            )
          );
        });

        this.ok = true;
      },
      error: () => {
        this.router.navigateByUrl('/seances');
      },
    });
  }
}