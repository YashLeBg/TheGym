import { User } from "./user";

export class Sportif extends User {
    constructor(
        id: number,
        email: string,
        password: string,
        nom: string,
        prenom: string,
        roles: string[],
        public dateInscription: Date,
        public niveauSportif: string,
        public seances: number[]
    ) {
        super(id, email, password, nom, prenom, roles);
    }
}