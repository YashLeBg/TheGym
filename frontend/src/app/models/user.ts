export class User {
    constructor(
        public id: number,
        public email: string,
        public password: string,
        public nom: string,
        public prenom: string,
        public roles: string[]
    ) { }
}