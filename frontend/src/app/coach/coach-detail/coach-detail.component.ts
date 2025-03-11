import { Component } from '@angular/core';
import { Coach } from '../../../models/coach';
import { CoachService } from '../../../services/coach.service';
import { SeanceService } from '../../../services/seance.service';
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
    private seanceService: SeanceService,
    private router: Router,
    private route: ActivatedRoute
  ) { }

  public ngOnInit(): void {
    const id: number = this.route.snapshot.params['id'];

    this.coachService.getCoach(id).subscribe({
      next: (coach) => {
        this.coach = coach;

        this.seanceService.getSeances().subscribe({
          next: (seances) => {
            this.seances = seances.filter((s) => s.coach === this.coach.id);
            this.ok = true;
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