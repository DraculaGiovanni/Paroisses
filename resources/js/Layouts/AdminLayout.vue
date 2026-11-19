<template>
  <div class="min-h-screen bg-cream-50 dark:bg-gray-950" :style="{ '--accent': $page.props.paroisse?.couleur_principale || '#1E40AF' }">
    <!-- Desktop Sidebar -->
    <aside
      :class="[
        'fixed inset-y-0 left-0 z-50 w-64 bg-white dark:bg-gray-900 border-r border-cream-300 dark:border-gray-800 transform transition-transform duration-300 lg:translate-x-0',
        sidebarOpen ? 'translate-x-0' : '-translate-x-full'
      ]"
    >
      <div class="flex flex-col h-full">
        <!-- Logo / Paroisse -->
        <div class="flex items-center justify-between h-16 px-6 border-b border-cream-300 dark:border-gray-800">
          <Link :href="route('admin.dashboard')" class="flex items-center gap-3">
            <div class="flex h-10 w-10 items-center justify-center rounded-full bg-spiritual-700 text-white">
              <IconChurch class="h-5 w-5" />
            </div>
            <div class="flex-1 min-w-0">
              <p class="truncate font-display text-lg leading-tight text-gray-900 dark:text-white">
                {{ paroisseName }}
              </p>
              <p class="text-[10px] uppercase tracking-[0.18em] text-gray-500 dark:text-gray-400">
                Administration
              </p>
            </div>
          </Link>
          
          <button
            @click="sidebarOpen = false"
            class="lg:hidden p-2 text-gray-500 hover:text-gray-700 dark:hover:text-gray-300"
          >
            <svg class="w-5 h-5" fill="none" viewBox="0 0 24 24" stroke="currentColor">
              <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M6 18L18 6M6 6l12 12" />
            </svg>
          </button>
        </div>
        
        <!-- Navigation -->
        <p class="px-6 pt-6 text-[10px] font-semibold uppercase tracking-[0.2em] text-gray-400 dark:text-gray-500">
          Menu
        </p>
        <nav class="flex-1 space-y-1 overflow-y-auto px-4 py-3">
          <NavLink
            v-for="item in navItems"
            :key="item.href"
            :href="item.href"
            :active="item.active"
          >
            <component :is="item.icon" class="w-5 h-5" />
            <span class="flex-1 text-left">{{ item.label }}</span>
            <span
              v-if="item.badge > 0"
              class="inline-flex min-w-5 items-center justify-center rounded-full bg-gold-500 px-1.5 py-0.5 text-xs font-semibold text-white"
            >
              {{ item.badge > 99 ? '99+' : item.badge }}
            </span>
          </NavLink>
        </nav>
        
        <!-- User menu -->
        <div class="p-4 border-t border-cream-300 dark:border-gray-800">
          <Dropdown align="top" width="48">
            <template #trigger>
              <button class="flex items-center gap-3 w-full p-2 rounded-lg hover:bg-cream-100 dark:hover:bg-gray-800 transition-colors">
                <div class="w-8 h-8 bg-spiritual-100 dark:bg-spiritual-900 rounded-full flex items-center justify-center">
                  <span class="text-spiritual-700 dark:text-spiritual-300 font-medium text-sm">
                    {{ $page.props.auth.user.name.charAt(0).toUpperCase() }}
                  </span>
                </div>
                <div class="flex-1 text-left min-w-0">
                  <p class="text-sm font-medium text-gray-900 dark:text-white truncate">
                    {{ $page.props.auth.user.name }}
                  </p>
                  <p class="text-xs text-gray-500 dark:text-gray-400">
                    {{ roleLabel }}
                  </p>
                </div>
              </button>
            </template>
            
            <template #content>
              <DropdownLink :href="route('profile.edit')">Profil</DropdownLink>
              <DropdownLink :href="route('logout')" method="post" as="button">
                Déconnexion
              </DropdownLink>
            </template>
          </Dropdown>

          <button
            type="button"
            @click="logout"
            class="mt-2 flex min-h-11 w-full items-center gap-3 rounded-lg px-3 py-2 text-sm font-medium text-gray-600 transition-colors hover:bg-cream-100 hover:text-gray-900 dark:text-gray-400 dark:hover:bg-gray-800 dark:hover:text-white"
          >
            <svg class="w-5 h-5" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="2">
              <path stroke-linecap="round" stroke-linejoin="round" d="M17 16l4-4m0 0l-4-4m4 4H7m6 4v1a3 3 0 01-3 3H6a3 3 0 01-3-3V7a3 3 0 013-3h4a3 3 0 013 3v1" />
            </svg>
            Déconnexion
          </button>
        </div>
      </div>
    </aside>
    
    <!-- Mobile bottom nav -->
    <nav class="lg:hidden fixed bottom-0 inset-x-0 z-40 bg-white dark:bg-gray-900 border-t border-cream-300 dark:border-gray-800 safe-area-inset-bottom">
      <div class="h-16 overflow-x-auto">
        <div class="flex h-full min-w-max">
        <MobileNavLink
          v-for="item in navItems"
          :key="item.href"
          :href="item.href"
          :active="item.active"
        >
          <span class="relative">
            <component :is="item.icon" class="w-6 h-6" />
            <span
              v-if="item.badge > 0"
              class="absolute -right-2 -top-1 inline-flex min-w-4 items-center justify-center rounded-full bg-gold-500 px-1 text-[10px] font-semibold leading-4 text-white"
            >
              {{ item.badge > 9 ? '9+' : item.badge }}
            </span>
          </span>
          <span class="text-xs mt-1 whitespace-nowrap">{{ item.label }}</span>
        </MobileNavLink>
        </div>
      </div>
    </nav>
    
    <!-- Main content -->
    <div class="lg:pl-64">
      <!-- Mobile header -->
      <header class="lg:hidden sticky top-0 z-30 bg-white dark:bg-gray-900 border-b border-cream-300 dark:border-gray-800">
        <div class="flex items-center justify-between h-16 px-4">
          <button
            @click="sidebarOpen = true"
            class="p-2 text-gray-500 hover:text-gray-700 dark:hover:text-gray-300"
          >
            <svg class="w-6 h-6" fill="none" viewBox="0 0 24 24" stroke="currentColor">
              <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M4 6h16M4 12h16M4 18h16" />
            </svg>
          </button>
          
          <h1 class="truncate font-display text-xl text-gray-900 dark:text-white">
            {{ pageTitle }}
          </h1>
          
          <button
            type="button"
            @click="logout"
            aria-label="Déconnexion"
            title="Déconnexion"
            class="flex h-10 w-10 items-center justify-center rounded-lg text-gray-500 hover:bg-cream-100 hover:text-gray-700 dark:hover:bg-gray-800 dark:hover:text-gray-300"
          >
            <svg class="w-5 h-5" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="2">
              <path stroke-linecap="round" stroke-linejoin="round" d="M17 16l4-4m0 0l-4-4m4 4H7m6 4v1a3 3 0 01-3 3H6a3 3 0 01-3-3V7a3 3 0 013-3h4a3 3 0 013 3v1" />
            </svg>
          </button>
        </div>
      </header>
      
      <main class="p-4 sm:p-6 lg:p-8 pb-20 lg:pb-8">
        <slot />
      </main>
    </div>
    
    <!-- Backdrop pour mobile sidebar -->
    <Transition
      enter-active-class="transition-opacity duration-300"
      enter-from-class="opacity-0"
      enter-to-class="opacity-100"
      leave-active-class="transition-opacity duration-200"
      leave-from-class="opacity-100"
      leave-to-class="opacity-0"
    >
      <div
        v-if="sidebarOpen"
        class="fixed inset-0 bg-gray-900/50 z-40 lg:hidden"
        @click="sidebarOpen = false"
      />
    </Transition>
    
    <!-- Toast container -->
    <Toast />
  </div>
</template>

<script setup>
import { ref, computed } from 'vue';
import { Link, router, usePage } from '@inertiajs/vue3';
import Dropdown from '@/Components/Dropdown.vue';
import DropdownLink from '@/Components/DropdownLink.vue';
import Toast from '@/Components/UI/Toast.vue';
import NavLink from '@/Components/Admin/NavLink.vue';
import MobileNavLink from '@/Components/Admin/MobileNavLink.vue';
import IconHome from '@/Components/Icons/IconHome.vue';
import IconBullhorn from '@/Components/Icons/IconBullhorn.vue';
import IconClock from '@/Components/Icons/IconClock.vue';
import IconHeart from '@/Components/Icons/IconHeart.vue';
import IconSettings from '@/Components/Icons/IconSettings.vue';
import IconChurch from '@/Components/Icons/IconChurch.vue';
import { useFlashMessages } from '@/Composables/useFlashMessages';

// Gérer les messages flash automatiquement
useFlashMessages();

const page = usePage();
const sidebarOpen = ref(false);

function logout() {
  router.post(route('logout'));
}

const paroisseName = computed(() => {
  return page.props.paroisse?.nom || 'Administration';
});

const roleLabel = computed(() => {
  const role = page.props.auth.user.role;
  return {
    super_admin: 'Super Admin',
    admin: 'Administrateur',
    secretariat: 'Secrétariat',
  }[role] || role;
});

const pageTitle = computed(() => {
  return page.props.title || 'Admin';
});

// Navigation items
const navItems = computed(() => {
  const isSuperAdmin = page.props.auth.user.role === 'super_admin';
  const hasParoisse = page.props.auth.user.paroisse_id !== null;
  const permissions = page.props.auth.user.permissions || [];

  const items = [
    {
      label: 'Accueil',
      href: route('admin.dashboard'),
      active: route().current('admin.dashboard'),
      icon: IconHome,
    },
    {
      label: 'Activité',
      href: route('admin.activites.index'),
      active: route().current('admin.activites.*'),
      icon: IconClock,
      adminOnly: true,
    },
    {
      label: 'Avis',
      href: route('admin.avis.index'),
      active: route().current('admin.avis.*'),
      icon: IconBullhorn,
      adminOnly: true,
    },
    {
      label: 'Annonces',
      href: route('admin.annonces.index'),
      active: route().current('admin.annonces.*'),
      icon: IconBullhorn,
    },
    {
      label: 'Horaires',
      href: route('admin.horaires.index'),
      active: route().current('admin.horaires.*'),
      icon: IconClock,
    },
  ];

  if (isSuperAdmin && !hasParoisse) {
    items.push({
      label: 'Paroisses',
      href: route('super.paroisses.index'),
      active: route().current('super.paroisses.*'),
      icon: IconChurch,
    });
  }

  if (isSuperAdmin || permissions.includes('intentions.gerer')) {
    items.push({
      label: 'Intentions',
      href: route('admin.intentions.index'),
      active: route().current('admin.intentions.*'),
      icon: IconHeart,
      badge: page.props.menu?.intentions_nouvelles || 0,
    });
  }

  if (isSuperAdmin || permissions.includes('liturgie.gerer')) {
    items.push({
      label: 'Liturgie',
      href: route('admin.liturgie.index'),
      active: route().current('admin.liturgie.*'),
      icon: IconChurch,
    });
  }

  if (isSuperAdmin || page.props.auth.user.role === 'admin') {
    items.push({
      label: 'Équipe',
      href: route('admin.equipe.index'),
      active: route().current('admin.equipe.*'),
      icon: IconSettings,
    });
  }

  if (isSuperAdmin || page.props.auth.user.role === 'admin') {
    items.push({
      label: 'Réglages',
      href: route('admin.paroisse.edit'),
      active: route().current('admin.paroisse.*'),
      icon: IconSettings,
    });
  }

  return items.filter((item) => !item.adminOnly
    || isSuperAdmin
    || page.props.auth.user.role === 'admin');
});
</script>
