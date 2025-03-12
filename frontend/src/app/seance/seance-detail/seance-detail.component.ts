import { Component } from '@angular/core';
import { Seance } from '../../../models/seance';
import { Coach } from '../../../models/coach';
import { Exercice } from '../../../models/exercice';
import { Sportif } from '../../../models/sportif';
import { AuthService } from '../../../services/auth.service';
import { SeanceService } from '../../../services/seance.service';
import { CoachService } from '../../../services/coach.service';
import { ExerciceService } from '../../../services/exercice.service';
import { ActivatedRoute, Router } from '@angular/router';

@Component({
  selector: 'app-seance-detail',
  templateUrl: './seance-detail.component.html',
  styleUrl: './seance-detail.component.css'
})

export class SeanceDetailComponent {
  public seance: Seance = new Seance(0, new Date(), '', '', '', '', { id: 0, nom: '', prenom: '', tarif_horaire: 0, specialites: [] }, [], []);
  public coach: Coach = new Coach(0, '', '', '', [], 0, []);
  public exercices: Exercice[] = [];

  public ok: boolean = false;
  public statut: string = '';

  constructor(
    private authService: AuthService,
    private seanceService: SeanceService,
    private coachService: CoachService,
    private exerciceService: ExerciceService,
    private route: ActivatedRoute,
    private router: Router
  ) { }

  public ngOnInit(): void {
    this.authService.currentAuthUser.subscribe({
      next: (authUser) => {
        if (!authUser.isLogged()) {
          this.router.navigateByUrl('/login');
        }
      }
    });

    const id: number = this.route.snapshot.params['id'];
    this.seanceService.getSeance(Number(id)).subscribe({
      next: (seance) => {
        this.seance = seance;
        this.ok = true;

        console.log(this.seance);

        this.coachService.getCoach(this.seance.coach.id).subscribe({
          next: (coach) => {
            this.coach = coach;

            this.exerciceService
              .getExercices()
              .subscribe({
                next: (exercices) => {
                  this.exercices = exercices;
                  this.exercices = this.exercices.filter((e) =>
                    this.seance.exercices.some((se) => se.id === e.id)
                  );
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
      error: () => {
        this.router.navigateByUrl('/seances');
      },
    });
  }
}