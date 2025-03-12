import { Seance } from "./seance";

export class Bilan {
    constructor(
        public seances: Seance[],
        public total_seances_mois: number,
        public total_seances: number,
        public theme_seances: {
            bodybuilding: number,
            crossfit: number,
            powerlifting: number,
            streetlifting: number,
            yoga: number,
            cardio: number,
            calisthenics: number
        },
        public top_3_exercices: { id: number, value: number }[],
        public duree_totale: number
    ) { }
}