<script setup>
import { Head, Link } from '@inertiajs/vue3'
import { onMounted } from 'vue'
import { router } from '@inertiajs/vue3'

onMounted(() => {
  setTimeout(() => {
    router.visit(route('home'))
  }, 3000) // Redirection après 3 secondes
})
</script>

<template>
  <Head>
    <title>DoniForm - Aw Bismillah</title>
  </Head>

  <div class="redirect-container">
    <div class="text-center redirect-content">
      <div class="spinner-border text-primary" style="width: 3rem; height: 3rem;" role="status">
        <span class="visually-hidden">Chargement...</span>
      </div>
      <h2 class="mt-4">Redirection en cours</h2>
      <p class="mt-2 text-muted">Vous serez redirigé vers la page d'accueil dans quelques instants...</p>
      <Link :href="route('home')" class="mt-4 btn btn-outline-primary">
        Cliquez ici si la redirection ne fonctionne pas
      </Link>
    </div>
  </div>
</template>

<style scoped>
.redirect-container {
  display: flex;
  justify-content: center;
  align-items: center;
  min-height: 100vh;
  background-color: #f8f9fa;
}

.redirect-content {
  max-width: 500px;
  padding: 2rem;
  background: white;
  border-radius: 10px;
  box-shadow: 0 0.5rem 1rem rgba(0, 0, 0, 0.1);
}

.spinner-border {
  display: inline-block;
  border: 0.25em solid currentColor;
  border-right-color: transparent;
  border-radius: 50%;
  animation: spinner-border 0.75s linear infinite;
}

@keyframes spinner-border {
  to { transform: rotate(360deg); }
}
</style>