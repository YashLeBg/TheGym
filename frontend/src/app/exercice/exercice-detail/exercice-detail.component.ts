import { Component } from '@angular/core';
import { Exercice } from '../../../models/exercice';
import { AuthService } from '../../../services/auth.service';
import { ExerciceService } from '../../../services/exercice.service';
import { ActivatedRoute, Router } from '@angular/router';
import { Seance } from '../../../models/seance';
import { SeanceService } from '../../../services/seance.service';

@Component({
  selector: 'app-exercice-detail',
  templateUrl: './exercice-detail.component.html',
  styleUrl: './exercice-detail.component.css'
})

export class ExerciceDetailComponent {
  public exercice: Exercice = new Exercice(0, '', '', 0, '', []);
  public seances: Seance[] = [];

  public ok: boolean = false;

  constructor(
    private authService: AuthService,
    private exerciceService: ExerciceService,
    private seanceService: SeanceService,
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
    this.exerciceService.getExercice(id).subscribe({
      next: (exercice) => {
        this.exercice = exercice;

        this.seanceService.getSeances().subscribe({
          next: (seances) => {
            this.seances = seances.filter((s) =>
              s.exercices.some((e) => e.id === this.exercice.id)
            );
            this.ok = true;
          },
          error: (error) => {
            console.error('Erreur lors du chargement des exercices :', error);
          },
        });

      },
      error: () => {
        this.router.navigateByUrl('/seances');
      },
    });
  }
}