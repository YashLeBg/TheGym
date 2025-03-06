import { Component } from '@angular/core';
import { Coach, CoachService } from '../../services/coach.service';
import { ActivatedRoute, Router } from '@angular/router';
import { Seance, SeanceService } from '../../services/seance.service';

@Component({
  selector: 'app-coach-detail',
  templateUrl: './coach-detail.component.html',
  styleUrl: './coach-detail.component.css',
})
export class CoachDetailComponent {
  public coach: Coach = new Coach();
  public seances: Seance[] = [];
  public ok: boolean = false;

  constructor(
    private coachService: CoachService,
    private seanceService: SeanceService,
    private router: Router,
    private route: ActivatedRoute
  ) {}

  public ngOnInit(): void {
    const id: number = this.route.snapshot.params['id'];
    this.coachService.getCoach(id).subscribe({
      next: (coach) => {
        this.coach = coach;

        this.seanceService.getSeances().subscribe({
          next: (seances) => {
            this.seances = seances.filter((s) => s.coach.id === this.coach.id);
            this.ok = true;
            console.log('Séances du coach :', this.seances);
          },
          error: (error) => {
            console.error('Erreur lors du chargement des séances :', error);
          },
        });
      },
      error: () => {
        this.router.navigateByUrl('/seances');
      },
    });
  }
}
