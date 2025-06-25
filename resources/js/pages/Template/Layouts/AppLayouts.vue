<script setup>
import { Link, router, Head } from '@inertiajs/vue3';
import { route } from 'ziggy-js';
import { ref, onMounted, computed, defineProps } from 'vue';
import { usePage } from '@inertiajs/vue3';

// États réactifs
const mounted = ref(false);
const isScrolled = ref(false);

// Cycle de vie
onMounted(() => {mounted.value = true;});
</script>

<template>
  <Head :title="title">
    <!-- Préchargement des assets -->
    <link rel="preload" href="/assets/vendor/bootstrap/js/bootstrap.bundle.min.js" as="script">
    <link rel="preload" href="/assets/js/main.js" as="script">
    
    <!-- CSS -->
    <link href="/assets/vendor/bootstrap/css/bootstrap.min.css" rel="stylesheet">
    <link href="/assets/vendor/bootstrap-icons/bootstrap-icons.css" rel="stylesheet">
    <link href="/assets/css/main.css" rel="stylesheet">
  </Head>

  <!-- Header -->
 <!-- En-tête -->
  <header id="header" class="header d-flex align-items-center fixed-top">
      <div class="container d-flex justify-content-between align-items-center">
        <Link
          :href="route('home')"
          class="logo d-flex align-items-center me-auto me-xl-0"
        >
          <h1 class="sitename">DoniForm</h1>
          <span>.</span>
        </Link>

        <nav id="navmenu" class="navmenu">
          <ul>
            <li>
              <Link
                :href="route('home')"
                :class="{ active: $page.url === '/' }"
              >
                Accueil
              </Link>
            </li>
            <li>
              <Link
                :href="route('program.index')"
                :class="{ active: $page.component.startsWith('Programs/') }"
              >
                Programmes
              </Link>
            </li>
            <li class="dropdown">
              <a href="#">
                <span>Mon compte</span>
                <i class="bi bi-chevron-down toggle-dropdown"></i>
              </a>
              <ul>
                <template v-if="$page.props.auth.user">
                  <li>
                    <Link :href="route('profile.edit')"> Mon profil </Link>
                  </li>
                  <li>
                    <Link :href="route('logout')" method="post" as="button">
                      Déconnexion
                    </Link>
                  </li>
                </template>
                <template v-else>
                  <li>
                    <Link
                      :href="route('login')"
                      :class="{ active: $page.component === 'Auth/Login' }"
                    >
                      Connexion
                    </Link>
                  </li>
                  <li>
                    <Link
                      :href="route('register')"
                      :class="{ active: $page.component === 'Auth/Register' }"
                    >
                      Inscription
                    </Link>
                  </li>
                </template>
              </ul>
            </li>
          </ul>
          <i class="mobile-nav-toggle d-xl-none bi bi-list"></i>
        </nav>
      </div>
    </header>

  <!-- Contenu principal -->
  <main class="main">
    <slot /> <!-- Injection du contenu des pages -->
  </main>

  <!-- Footer -->
  <footer id="footer" class="footer light-background">
    <div class="container footer-top">
      <div class="row gy-4">
        <div class="col-lg-4 col-md-6 footer-about">
          <Link :href="route('home')" class="logo d-flex align-items-center">
            <span class="sitename">DoniForm</span>
          </Link>
          <p class="pt-3"><strong>L'incubateur de référence au Mali</strong></p>
          <div class="mt-4 social-links d-flex">
            <a href="#"><i class="bi bi-twitter-x"></i></a>
            <a href="#"><i class="bi bi-facebook"></i></a>
            <a href="#"><i class="bi bi-instagram"></i></a>
            <a href="#"><i class="bi bi-linkedin"></i></a>
          </div>
        </div>

        <div class="col-lg-2 col-md-3 footer-links">
          <h4>Liens Utiles</h4>
          <ul>
            <li><Link :href="route('home')">Accueil</Link></li>
            <li><Link :href="route('program.index')">Programmes</Link></li>
          </ul>
        </div>

        <div class="col-lg-2 col-md-3 footer-links">
          <h4>Coordonnées</h4>
          <ul>
            <li><a href="mailto:contact@doniform.ml">contact@doniform.ml</a></li>
          </ul>
        </div>
      </div>
    </div>

    <div class="container mt-4 text-center copyright">
      <p>
        © <span>{{ new Date().getFullYear() }}</span>
        <strong class="px-1 sitename">DoniForm</strong>
        - Tous droits réservés.
      </p>
      <div class="credits">
        Designed by <a href="https://doucsoft.com">DoucsoftTechnologie</a>
      </div>
    </div>
  </footer>

  <!-- Scroll Top -->
  <a href="#" id="scroll-top" class="scroll-top d-flex align-items-center justify-content-center" 
     :class="{ active: isScrolled }"
     @click.prevent="window.scrollTo({ top: 0, behavior: 'smooth' })">
    <i class="bi bi-arrow-up-short"></i>
  </a>

  <!-- Scripts -->
  <component is="script" src="/assets/vendor/bootstrap/js/bootstrap.bundle.min.js" defer></component>
  <component is="script" src="/assets/vendor/aos/aos.js" defer></component>
  <component is="script" src="/assets/vendor/glightbox/js/glightbox.min.js" defer></component>
  <component is="script" src="/assets/js/main.js" defer></component>
</template>