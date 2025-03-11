export class Seance {
    constructor(
        public id: number,
        public date_heure: Date,
        public type_seance: string,
        public theme_seance: string,
        public statut: string,
        public niveau_seance: string,
        public coach: number,
        public sportifs: number[],
        public exercices: { id: number, nom: string }[]
    ) { }
}