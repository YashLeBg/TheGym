import { Component, Input } from '@angular/core';
import { Coach } from '../../../models/coach';

@Component({
  selector: 'app-coach-item',
  templateUrl: './coach-item.component.html',
  styleUrl: './coach-item.component.css'
})

export class CoachItemComponent {
  @Input() coach!: Coach;

  constructor() { }
}