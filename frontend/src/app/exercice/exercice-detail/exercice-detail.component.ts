import { Component } from '@angular/core';
import { Seance, SeanceService } from '../../services/seance.service';
import { ActivatedRoute, Router } from '@angular/router';
import { Exercice, ExerciceService } from '../../services/exercice.service';

@Component({
  selector: 'app-exercice-detail',
  templateUrl: './exercice-detail.component.html',
  styleUrl: './exercice-detail.component.css',
})
export class ExerciceDetailComponent {
  public exercice: Exercice = new Exercice();
  public seances: Seance[] = [];
  public ok: boolean = false;

  constructor(
    private exerciceService: ExerciceService,
    private seanceService: SeanceService,
    private router: Router,
    private route: ActivatedRoute
  ) {}

  public ngOnInit(): void {
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
