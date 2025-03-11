export class Exercice {
    constructor(
        public id: number,
        public nom: string,
        public description: string,
        public duree_estimee: number,
        public difficulte: string,
        public seances: {
            id: number,
            date_heure: Date,
            type_seance: string,
            theme_seance: string
        }[],
    ) { }
}