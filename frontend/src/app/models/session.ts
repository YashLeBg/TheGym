export interface Session {
  id: number;
  title: string;
  start: Date | string;
  end: Date | string;
  description?: string;
  coachId?: number;
  coachName?: string;
  currentParticipants?: number;
  color?: string;
  statut?: string; // Statut de la séance (prevue, annulee, terminee, etc.)
  isUserRegistered?: boolean; // Indique si l'utilisateur courant est inscrit à cette séance
  
  // Champs supplémentaires potentiels de l'API
  nom?: string;         // Si l'API utilise 'nom' au lieu de 'title'
  debut?: Date | string; // Si l'API utilise 'debut' au lieu de 'start'
  fin?: Date | string;   // Si l'API utilise 'fin' au lieu de 'end'
  coach?: string | any; // Si l'API utilise 'coach' au lieu de 'coachName'
  participants?: number | { id: number; nom?: string; prenom?: string; }[]; // Nombre ou liste des participants
  
  // Nouveaux champs spécifiques à l'API
  date_heure?: string;  // Format ISO pour la date et l'heure de début
  duree?: number;       // Durée en minutes
  type?: string;        // Type de séance (peut être utilisé comme titre)
  coach_id?: number;    // ID du coach
  nb_participants?: number;  // Nombre actuel de participants
  
  // Nouveaux champs selon l'exemple fourni
  type_seance?: string;  // Type de séance (individuelle, groupe, etc.)
  theme_seance?: string; // Thème de la séance (powerlifting, yoga, etc.)
  niveau_seance?: string; // Niveau de la séance (debutant, intermediaire, avance)
  sportifs?: { id: number; nom?: string; prenom?: string; }[];  // Liste des sportifs inscrits
  exercices?: any[];    // Liste des exercices prévus
} 