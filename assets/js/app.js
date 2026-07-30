/* Fonctions communes : l'authentification et les donnees viennent de PHP/MySQL. */
const App = {
  reclamations: [],
  api(path, options = {}) { return fetch(path, {credentials:'same-origin', ...options}); },
  getSession() { const raw=sessionStorage.getItem('bmce_session'); return raw ? JSON.parse(raw) : null; },
  setSession(user) { sessionStorage.setItem('bmce_session', JSON.stringify(user)); },
  clearSession() { sessionStorage.removeItem('bmce_session'); },
  async loadReclamations() {
    const res=await this.api('../backend/api/reclamations.php'); const json=await res.json();
    if (!res.ok) throw new Error(json.message || 'Erreur de chargement');
    this.reclamations=json.reclamations || json.data || []; return this.reclamations;
  },
  requireAuth(roles) {
    const user=this.getSession();
    if (!user) { window.location.href='../login.html?redirect='+encodeURIComponent(location.pathname+location.search); return null; }
    if (roles && !roles.includes(user.role)) { window.location.href=user.role==='admin'?'../admin/dashboard.html':'../client/dashboard.html'; return null; }
    return user;
  },
  async logout() { try { await this.api('../backend/api/logout.php'); } finally { this.clearSession(); window.location.href='../index.html'; } },
  showToast(message, type='success') { const el=document.createElement('div'); el.textContent=message; el.className='toast '+type; document.body.appendChild(el); setTimeout(()=>el.remove(),3000); },
  formatDate(s) { return new Date(s).toLocaleDateString('fr-FR',{day:'2-digit',month:'2-digit',year:'numeric'}); },
  formatDateTime(s) { return new Date(s).toLocaleDateString('fr-FR',{day:'2-digit',month:'2-digit',year:'numeric'})+' a '+new Date(s).toLocaleTimeString('fr-FR',{hour:'2-digit',minute:'2-digit'}); },
  getTypeLabel(t) { return {credit_immobilier:'Credit immobilier',credit_auto:'Credit auto',credit_conso:'Credit consommation',credit_professionnel:'Credit professionnel',autre:'Autre'}[t]||t; },
  getGraviteLabel(g) { return {basse:'Basse',moyenne:'Moyenne',haute:'Haute',critique:'Critique'}[g]||g; },
  getStatutLabel(s) { return {attente:'En attente',encours:'En cours',prise_charge:'Prise en charge',demande_supplementaire:'Demande supplémentaire',resolu:'Résolue',cloture:'Clôturée'}[s]||s; },
  getStatutIcon(s) { return {attente:'◷',encours:'↻',prise_charge:'✓',demande_supplementaire:'⚠',resolu:'✓',cloture:'✓'}[s]||''; },
  getStatutBadge(s) { return 'badge-'+s; }, getGraviteBadge(g) { return 'badge-'+g; },
  initSidebar() {
    document.querySelector('.menu-toggle')?.addEventListener('click',()=>document.querySelector('.sidebar')?.classList.toggle('open'));
    document.getElementById('btn-logout')?.addEventListener('click',e=>{e.preventDefault();this.logout();});
    const s=this.getSession(); if(s){ const n=document.getElementById('sidebar-user-name'),r=document.getElementById('sidebar-user-role'),a=document.getElementById('sidebar-user-avatar'); if(n)n.textContent=`${s.prenom} ${s.nom}`; if(r)r.textContent=s.role==='admin'?'Administrateur':'Client'; if(a)a.textContent=(s.prenom[0]+s.nom[0]).toUpperCase(); }
  }
};
document.addEventListener('DOMContentLoaded',()=>{ if(document.querySelector('.sidebar')) App.initSidebar(); });
