import { defineComponent, ref } from 'vue';
import { useRouter } from 'vue-router';

export default defineComponent({
  setup() {
    const router = useRouter();
    const username = ref('');
    const password = ref('');

    const login = () => {
      // TODO: Replace with real backend login API call
      if (username.value && password.value) {
        localStorage.setItem('loggedIn', 'true');
        router.push('/');
      }
    };

    return { username, password, login };
  }
});