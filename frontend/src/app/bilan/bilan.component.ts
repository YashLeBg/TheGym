import { Component, ElementRef, ViewChild } from '@angular/core';
import { Router } from '@angular/router';
import { filter } from 'rxjs';
import Chart from 'chart.js/auto';

import { AuthService } from '../../services/auth.service';
import { SportifService } from '../../services/sportif.service';
import { Bilan } from '../../models/bilan';
import { ExerciceService } from '../../services/exercice.service';
import { Exercice } from '../../models/exercice';

@Component({
  selector: 'app-bilan',
  templateUrl: './bilan.component.html',
  styleUrl: './bilan.component.css'
})

export class BilanComponent {
  public bilan?: Bilan;
  public date: string = new Intl.DateTimeFormat('fr-FR', { month: 'long' }).format(new Date());
  public topExercices: { exercice: Exercice, value: number }[] = [];

  @ViewChild('myChart') myChart!: ElementRef;
  chart!: Chart;

  constructor(
    private authService: AuthService,
    private sportifService: SportifService,
    private exerciceService: ExerciceService,
    private router: Router
  ) { }

  public ngOnInit() {
    this.authService.currentAuthUser.pipe(filter(user => user.id !== 0)).subscribe((user) => {
      if (!user.isLogged()) {
        this.router.navigate(['/login']);
        return;
      }

      this.sportifService.getBilan(user.id).subscribe((bilan) => {
        this.bilan = bilan;
        this.bilan.seances.sort((a, b) => b.date_heure.toString().localeCompare(a.date_heure.toString()));

        this.exerciceService.getExercice(bilan.top_3_exercices[0].id).subscribe((exercice) => {
          this.topExercices.push({ exercice: exercice, value: bilan.top_3_exercices[0].value });

          this.exerciceService.getExercice(bilan.top_3_exercices[1].id).subscribe((exercice) => {
            this.topExercices.push({ exercice: exercice, value: bilan.top_3_exercices[1].value });

            this.exerciceService.getExercice(bilan.top_3_exercices[2].id).subscribe((exercice) => {
              this.topExercices.push({ exercice: exercice, value: bilan.top_3_exercices[2].value });
            });
          });
        });

        this.loadPieChart();
      });
    });
  }

  private loadPieChart() {
    this.chart = new Chart(this.myChart.nativeElement, {
      type: 'pie',
      data: {
        labels: [
          'BODYBUILDING',
          'CROSSFIT',
          'POWERLIFTING',
          'STREETLIFTING',
          'YOGA',
          'CARDIO',
          'CALISTHENICS'
        ],
        datasets: [{
          data: [
            this.bilan!.theme_seances.bodybuilding,
            this.bilan!.theme_seances.crossfit,
            this.bilan!.theme_seances.powerlifting,
            this.bilan!.theme_seances.streetlifting,
            this.bilan!.theme_seances.yoga,
            this.bilan!.theme_seances.cardio,
            this.bilan!.theme_seances.calisthenics
          ],
          backgroundColor: [
            '#795548',
            '#FF5722',
            '#9C27B0',
            '#2196F3',
            '#4CAF50',
            '#E91E63',
            '#00BCD4'
          ]
        }]
      },
      options: {
        responsive: true
      }
    });
  }
}