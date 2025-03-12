export class User {
    constructor(
        public id: number,
        public nom: string,
        public prenom: string,
        public email: string,
        public roles: string[]
    ) { }

    public isLogged(): boolean {
        return this.email.length > 0 && this.roles.includes('ROLE_SPORTIF');
    }
}