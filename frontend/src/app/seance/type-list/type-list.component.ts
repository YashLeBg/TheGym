import { Component } from '@angular/core';

@Component({
  selector: 'app-type-list',
  templateUrl: './type-list.component.html',
  styleUrl: './type-list.component.css'
})
export class TypeListComponent {
  public types: { name: string, description: string }[] = [
    { name: 'bodybuilding', description: 'Construire du muscle afin de devenir le plus massif' },
    { name: 'crossfit', description: 'Enchaîner des exercices de force et d\'endurance' },
    { name: 'powerlifting', description: 'S\'entraîner pour devenir le plus fort sur un SBD' },
    { name: 'streetlifting', description: 'S\'entraîner pour flex dans la rue' },
    { name: 'yoga', description: 'Se relaxer et se muscler (ou pas)' },
    { name: 'cardio', description: 'Travailler son endurance' },
    { name: 'calisthenics', description: 'Se muscler avec le poids du corps' }
  ];

  constructor() { }
}