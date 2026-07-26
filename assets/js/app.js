/* BMCE Réclamations — Core JS */

const App = {
  /* Mock user database (sera remplacé par PHP/MySQL) */
  users: {
    client: {
      email: 'client@bmce.ma',
      password: 'client123',
      role: 'client',
      nom: 'Client',
      prenom: '1',
      cin: 'AB123456',
      telephone: '0612345678',
      email_display: 'client1@email.ma',
      numero_compte: '007 780 0001234567 00'
    },
    admin: {
      email: 'admin@bmce.ma',
      password: 'admin123',
      role: 'admin',
      nom: 'Admin',
      prenom: '1',
      poste: 'Gestionnaire Réclamations'
    }
  },

  /* Mock réclamations */
  reclamations: [
    {
      id: 1,
      reference: 'REC-2026-1042',
      client_id: 'client',
      nom: 'Client', prenom: '1',
      objet: 'Retard de traitement dossier crédit immobilier',
      type: 'credit_immobilier',
      type_label: 'Crédit immobilier',
      gravite: 'haute',
      gravite_label: 'Haute',
      statut: 'encours',
      statut_label: 'En cours',
      portee: 'dossier',
      numero_dossier: 'CRD-2025-8891',
      detail: 'Mon dossier de crédit immobilier soumis le 15 janvier 2026 n\'a toujours pas reçu de réponse. J\'ai fourni tous les documents demandés.',
      piece_jointe: 'justificatif_revenus.pdf',
      date_creation: '2026-02-10T09:30:00',
      date_modification: '2026-02-12T14:20:00',
      memos: [
        { auteur: 'Admin 1', role: 'admin', date: '2026-02-11T10:00:00', message: 'Dossier transmis à l\'agence Casablanca Maarif pour vérification.' },
        { auteur: 'Agence Maarif', role: 'agence', date: '2026-02-12T14:20:00', message: 'Documents complémentaires reçus. Analyse en cours, réponse sous 48h.' }
      ]
    },
    {
      id: 2,
      reference: 'REC-2026-1038',
      client_id: 'client',
      nom: 'Client', prenom: '1',
      objet: 'Erreur sur montant mensualité crédit auto',
      type: 'credit_auto',
      type_label: 'Crédit auto',
      gravite: 'moyenne',
      gravite_label: 'Moyenne',
      statut: 'attente',
      statut_label: 'En attente',
      portee: 'general',
      numero_dossier: null,
      detail: 'La mensualité prélevée ce mois est supérieure de 450 DH au montant convenu dans mon contrat.',
      piece_jointe: 'releve_bancaire.pdf',
      date_creation: '2026-02-05T11:15:00',
      date_modification: '2026-02-08T09:00:00',
      memos: [
        { auteur: 'Admin 1', role: 'admin', date: '2026-02-06T08:30:00', message: 'Réclamation enregistrée. Vérification auprès du service crédit.' }
      ]
    },
    {
      id: 3,
      reference: 'REC-2026-1025',
      client_id: 'client',
      nom: 'Client', prenom: '1',
      objet: 'Demande de rééchelonnement crédit consommation',
      type: 'credit_conso',
      type_label: 'Crédit consommation',
      gravite: 'basse',
      gravite_label: 'Basse',
      statut: 'resolu',
      statut_label: 'Résolu',
      portee: 'dossier',
      numero_dossier: 'CRD-2024-5520',
      detail: 'Suite à un changement de situation professionnelle, je souhaite un rééchelonnement de mon crédit consommation.',
      piece_jointe: null,
      date_creation: '2026-01-20T16:45:00',
      date_modification: '2026-01-28T10:30:00',
      memos: [
        { auteur: 'Admin 1', role: 'admin', date: '2026-01-22T09:00:00', message: 'Demande étudiée. Proposition de rééchelonnement envoyée.' },
        { auteur: 'Agence Rabat Agdal', role: 'agence', date: '2026-01-28T10:30:00', message: 'Client a signé l\'avenant. Dossier clôturé.' }
      ]
    },
    {
      id: 4,
      reference: 'REC-2026-1055',
      client_id: 'other',
      nom: 'Client', prenom: '2',
      objet: 'Refus crédit sans justification',
      type: 'credit_immobilier',
      type_label: 'Crédit immobilier',
      gravite: 'critique',
      gravite_label: 'Critique',
      statut: 'nouveau',
      statut_label: 'Nouveau',
      portee: 'general',
      numero_dossier: null,
      detail: 'Ma demande de crédit immobilier a été refusée sans explication détaillée malgré un dossier complet.',
      piece_jointe: 'dossier_credit.pdf',
      date_creation: '2026-02-14T08:00:00',
      date_modification: '2026-02-14T08:00:00',
      memos: []
    }
  ],

  /* Session */
  getSession() {
    const data = sessionStorage.getItem('bmce_session');
    return data ? JSON.parse(data) : null;
  },

  setSession(user) {
    sessionStorage.setItem('bmce_session', JSON.stringify(user));
  },

  clearSession() {
    sessionStorage.removeItem('bmce_session');
  },

  login(email, password) {
    const user = Object.values(this.users).find(item => item.email === email && item.password === password);
    if (user) {
      const session = { ...user };
      delete session.password;
      this.setSession(session);
      return session;
    }
    return null;
  },

  logout() {
    this.clearSession();
    window.location.href = '../index.html';
  },

  requireAuth(allowedRoles) {
    const session = this.getSession();
    if (!session) {
      const returnUrl = encodeURIComponent(window.location.pathname + window.location.search);
      window.location.href = `../login.html?redirect=${returnUrl}`;
      return null;
    }
    if (allowedRoles && !allowedRoles.includes(session.role)) {
      window.location.href = session.role === 'admin' ? '../admin/dashboard.html' : '../client/dashboard.html';
      return null;
    }
    return session;
  },

  /* Helpers */
  formatDate(dateStr) {
    const d = new Date(dateStr);
    return d.toLocaleDateString('fr-FR', { day: '2-digit', month: '2-digit', year: 'numeric' });
  },

  formatDateTime(dateStr) {
    const d = new Date(dateStr);
    return d.toLocaleDateString('fr-FR', { day: '2-digit', month: '2-digit', year: 'numeric' }) +
      ' à ' + d.toLocaleTimeString('fr-FR', { hour: '2-digit', minute: '2-digit' });
  },

  generateReference() {
    const year = new Date().getFullYear();
    const num = 1000 + this.reclamations.length + Math.floor(Math.random() * 100);
    return `REC-${year}-${num}`;
  },

  getStatutBadge(statut) {
    const map = {
      nouveau: 'badge-nouveau',
      encours: 'badge-encours',
      attente: 'badge-attente',
      resolu: 'badge-resolu',
      rejete: 'badge-rejete'
    };
    return map[statut] || 'badge-nouveau';
  },

  getGraviteBadge(gravite) {
    const map = {
      basse: 'badge-basse',
      moyenne: 'badge-moyenne',
      haute: 'badge-haute',
      critique: 'badge-critique'
    };
    return map[gravite] || 'badge-basse';
  },

  getTypeLabel(type) {
    const map = {
      credit_immobilier: 'Crédit immobilier',
      credit_auto: 'Crédit auto',
      credit_conso: 'Crédit consommation',
      credit_professionnel: 'Crédit professionnel',
      autre: 'Autre'
    };
    return map[type] || type;
  },

  getStatutLabel(statut) {
    const map = {
      nouveau: 'Nouveau',
      encours: 'En cours',
      attente: 'En attente',
      resolu: 'Résolu',
      rejete: 'Rejeté'
    };
    return map[statut] || statut;
  },

  getGraviteLabel(gravite) {
    const map = {
      basse: 'Basse',
      moyenne: 'Moyenne',
      haute: 'Haute',
      critique: 'Critique'
    };
    return map[gravite] || gravite;
  },

  showToast(message, type = 'success') {
    let container = document.querySelector('.toast-container');
    if (!container) {
      container = document.createElement('div');
      container.className = 'toast-container';
      document.body.appendChild(container);
    }
    const toast = document.createElement('div');
    toast.className = `toast ${type}`;
    toast.textContent = message;
    container.appendChild(toast);
    setTimeout(() => toast.remove(), 3500);
  },

  initSidebar() {
    const toggle = document.querySelector('.menu-toggle');
    const sidebar = document.querySelector('.sidebar');
    if (toggle && sidebar) {
      toggle.addEventListener('click', () => sidebar.classList.toggle('open'));
    }

    const currentPage = window.location.pathname.split('/').pop();
    document.querySelectorAll('.nav-item').forEach(item => {
      const href = item.querySelector('a')?.getAttribute('href');
      if (href === currentPage) item.classList.add('active');
    });

    const logoutBtn = document.getElementById('btn-logout');
    if (logoutBtn) {
      logoutBtn.addEventListener('click', (e) => {
        e.preventDefault();
        App.logout();
      });
    }

    const session = App.getSession();
    if (session) {
      const nameEl = document.getElementById('sidebar-user-name');
      const roleEl = document.getElementById('sidebar-user-role');
      const avatarEl = document.getElementById('sidebar-user-avatar');
      if (nameEl) nameEl.textContent = `${session.prenom} ${session.nom}`;
      if (roleEl) roleEl.textContent = session.role === 'admin' ? 'Administrateur' : 'Client';
      if (avatarEl) avatarEl.textContent = (session.prenom[0] + session.nom[0]).toUpperCase();
    }
  }
};

document.addEventListener('DOMContentLoaded', () => {
  if (document.querySelector('.sidebar')) {
    App.initSidebar();
  }
});
