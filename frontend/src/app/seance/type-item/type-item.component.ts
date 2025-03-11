import { Component, Input } from '@angular/core';

@Component({
  selector: 'app-type-item',
  templateUrl: './type-item.component.html',
  styleUrl: './type-item.component.css'
})

export class TypeItemComponent {
  @Input() type!: { name: string, description: string };

  constructor() { }
}