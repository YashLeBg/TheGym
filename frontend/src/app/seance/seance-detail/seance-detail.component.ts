import { Component } from '@angular/core';
import { Seance, SeanceService } from '../../services/seance.service';
import { ActivatedRoute, Router } from '@angular/router';
import { Coach, CoachService } from '../../services/coach.service';
import { Exercice, ExerciceService } from '../../services/exercice.service';
import { Sportif, SportifService } from '../../services/sportif.service';

@Component({
  selector: 'app-seance-detail',
  templateUrl: './seance-detail.component.html',
  styleUrl: './seance-detail.component.css',
})
export class SeanceDetailComponent {
  public seance: Seance = new Seance();
  public coach: Coach = new Coach();
  public exercices: Exercice[] = [];
  public nomsExercices: string = '';
  public sportifs: Sportif[] = [];
  public nomsSportifs: string = '';
  public ok: boolean = false;
  public statut: string = '';

  constructor(
    private seanceService: SeanceService,
    private coachService: CoachService,
    private exerciceService: ExerciceService,
    private sportifService: SportifService,
    private router: Router,
    private route: ActivatedRoute
  ) {}

  public ngOnInit(): void {
    const id: number = this.route.snapshot.params['id'];
    this.seanceService.getSeance(Number(id)).subscribe({
      next: (seance) => {
        this.seance = seance;
        this.ok = true;

        this.coachService.getCoach(this.seance.coach.id).subscribe({
          next: (coach) => {
            this.coach = coach;

            this.exerciceService
              .getExercicesByIds(this.seance.exercices.map((e) => e.id))
              .subscribe({
                next: (exercices) => {
                  this.exercices = exercices;
                  this.nomsExercices = this.exercices
                    .map((e) => e.nom)
                    .join(', ');

                  this.sportifService
                    .getSportifsByIds(this.seance.sportifs.map((s) => s.id))
                    .subscribe({
                      next: (sportifs) => {
                        this.sportifs = sportifs;
                        this.nomsSportifs = this.sportifs
                          .map((s) => `${s.nom} ${s.prenom}`)
                          .join(', ');
                      },
                    });
                },
                error: (error) => {
                  console.error(
                    'Erreur lors du chargement des exercices :',
                    error
                  );
                },
              });
          },
          error: (error) => {
            console.error('Erreur lors du chargement du coach :', error);
          },
        });
      },
      error: (error) => {
        this.router.navigateByUrl('/seances');
      },
    });
  }
}
