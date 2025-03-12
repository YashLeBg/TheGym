export class Seance {
    constructor(
        public id: number,
        public date_heure: Date,
        public type_seance: string,
        public theme_seance: string,
        public statut: string,
        public niveau_seance: string,
        public coach: {
            id: number,
            nom: string,
            prenom: string,
            tarif_horaire: number,
            specialites: string[]
        },
        public sportifs: {
            id: number,
            nom: string,
            prenom: string
        }[],
        public exercices: {
            id: number,
            nom: string,
            description: string
        }[]
    ) { }
}