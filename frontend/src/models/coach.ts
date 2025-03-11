export class Coach {
    constructor(
        public id: number,
        public email: string,
        public nom: string,
        public prenom: string,
        public specialites: string[],
        public tarif_horaire: number,
        public seances: {
            id: number,
            date_heure: Date,
            type_seance: string,
            theme_seance: string,
            exercices: {
                id: number,
                nom: string
            }[],
            statut: string,
            niveau_seance: string
        }[]
    ) { }
}