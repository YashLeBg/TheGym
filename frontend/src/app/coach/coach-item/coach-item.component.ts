import { Component, Input } from '@angular/core';
import { Coach, CoachService } from '../../services/coach.service';

@Component({
  selector: 'app-coach-item',
  templateUrl: './coach-item.component.html',
  styleUrl: './coach-item.component.css',
})
export class CoachItemComponent {
  @Input() coach: Coach = new Coach();

  constructor(private coachService: CoachService) {}
}
