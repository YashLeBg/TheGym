export class Sportif {
    constructor(
        public id: number,
        public email: string,
        public password: string,
        public nom: string,
        public prenom: string,
        public dateInscription: Date,
        public niveau: string,
        public seances: Array<number>
    ) { }
}