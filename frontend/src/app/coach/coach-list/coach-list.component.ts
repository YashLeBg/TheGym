import { Component } from '@angular/core';
import { Coach, CoachService } from '../../services/coach.service';

@Component({
  selector: 'app-coach-list',
  templateUrl: './coach-list.component.html',
  styleUrl: './coach-list.component.css',
})
export class CoachListComponent {
  public coachs: Coach[] = [];
  public ok: boolean = false;
  public okBDD: boolean = false;

  constructor(private coachService: CoachService) {}

  public ngOnInit(): void {
    this.coachService.getCoachs().subscribe({
      next: (coachs) => {
        this.coachs = coachs;
        this.ok = true;
        this.okBDD = true;
      },
      error: (error) => {
        this.ok = true;
        console.error('Erreur lors du chargement des coachs :', error);
      },
    });
  }
}
