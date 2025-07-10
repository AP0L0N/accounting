import { defineComponent, ref, computed, onMounted } from 'vue';
import { Company } from '../models/Company';
import { Invoice } from '../models/Invoice';
import { InvoiceItem } from '../models/InvoiceItem';

export default defineComponent({
  setup() {
    const companies = ref<Company[]>([]);
    const invoices = ref<Invoice[]>([]);
    const form = ref({
      date: '',
      currency: 'EUR',
      companyId: '',
      items: [] as {title: string, value: number}[]
    });
    const newCompany = ref({ name: '', address: '', vatId: '' });
    const showNewCompanyModal = ref(false);
    const selectedInvoice = ref<Invoice | null>(null);
    const editingId = ref<string | null>(null);
    const defaultCurrency = 'EUR';
    const expandedYears = ref(new Set([new Date().getFullYear()]));

    onMounted(() => {
      loadData();
    });

    const loadData = () => {
      // TODO: Replace with API calls to fetch companies and invoices from backend
      const storedCompanies = localStorage.getItem('companies');
      if (storedCompanies) {
        companies.value = JSON.parse(storedCompanies).map((c: any) => new Company(c.id, c.name, c.address, c.vatId));
      }
      const storedInvoices = localStorage.getItem('invoices');
      if (storedInvoices) {
        invoices.value = JSON.parse(storedInvoices).map((i: any) => new Invoice(i.id, i.date, i.currency, i.companyId, i.items.map((it: any) => new InvoiceItem(it.title, it.value)), new Date(i.createdAt)));
      }
    };

    const saveData = () => {
      // TODO: Remove this function; use API calls for saving data instead
      localStorage.setItem('companies', JSON.stringify(companies.value));
      localStorage.setItem('invoices', JSON.stringify(invoices.value));
    };

    const addCompany = () => {
      // TODO: Call backend API to add new company
      const id = Date.now().toString();
      companies.value.push(new Company(id, newCompany.value.name, newCompany.value.address, newCompany.value.vatId));
      form.value.companyId = id;
      newCompany.value = { name: '', address: '', vatId: '' };
      showNewCompanyModal.value = false;
      saveData();
    };

    const addItem = () => {
      form.value.items.push({ title: '', value: 0 });
    };

    const removeItem = (index: number) => {
      form.value.items.splice(index, 1);
    };

    const submitInvoice = () => {
      // TODO: Call backend API to create or update invoice
      const items = form.value.items.map(it => new InvoiceItem(it.title, it.value));
      if (editingId.value) {
        const index = invoices.value.findIndex(i => i.id === editingId.value);
        if (index !== -1) {
          invoices.value[index] = new Invoice(editingId.value, form.value.date, form.value.currency, form.value.companyId, items, invoices.value[index].createdAt);
        }
        editingId.value = null;
      } else {
        const id = Date.now().toString();
        invoices.value.push(new Invoice(id, form.value.date, form.value.currency, form.value.companyId, items));
      }
      resetForm();
      saveData();
    };

    const resetForm = () => {
      form.value = {
        date: '',
        currency: 'EUR',
        companyId: '',
        items: []
      };
    };

    const cancelEdit = () => {
      editingId.value = null;
      resetForm();
    };

    const editInvoice = (invoice: Invoice) => {
      editingId.value = invoice.id;
      form.value.date = invoice.date;
      form.value.currency = invoice.currency;
      form.value.companyId = invoice.companyId;
      form.value.items = invoice.items.map(it => ({ title: it.title, value: it.value }));
    };

    const deleteInvoice = (id: string) => {
      // TODO: Call backend API to delete invoice
      invoices.value = invoices.value.filter(i => i.id !== id);
      saveData();
    };

    const viewInvoice = (invoice: Invoice) => {
      selectedInvoice.value = invoice;
    };

    const getCompanyName = (id: string) => {
      const company = companies.value.find(c => c.id === id);
      return company ? company.name : 'Unknown';
    };

    const groupedInvoices = computed(() => {
      const groups: { [year: string]: Invoice[] } = {};
      invoices.value.forEach(inv => {
        const year = inv.createdAt.getFullYear().toString();
        if (!groups[year]) groups[year] = [];
        groups[year].push(inv);
      });
      return groups;
    });

    const yearTotals = computed(() => {
      const totals: { [year: string]: number } = {};
      for (const year in groupedInvoices.value) {
        totals[year] = groupedInvoices.value[year].reduce((sum, inv) => sum + inv.total, 0);
      }
      return totals;
    });

    const toggleYear = (year: string) => {
      if (expandedYears.value.has(year)) {
        expandedYears.value.delete(year);
      } else {
        expandedYears.value.add(year);
      }
    };

    const quarters = computed(() => {
      const q: { [key: string]: { total: number, tax: number } } = {};
      const quarterNames = ['Q1 (Jan-Mar)', 'Q2 (Apr-Jun)', 'Q3 (Jul-Sep)', 'Q4 (Oct-Dec)'];
      invoices.value.forEach(inv => {
        const month = inv.createdAt.getMonth();
        const qIndex = Math.floor(month / 3);
        const qName = `${inv.createdAt.getFullYear()} ${quarterNames[qIndex]}`;
        if (!q[qName]) q[qName] = { total: 0, tax: 0 };
        q[qName].total += inv.total;
        q[qName].tax = Math.round(q[qName].total * 0.22 * 100) / 100;
      });
      return q;
    });

    return {
      companies,
      form,
      newCompany,
      showNewCompanyModal,
      selectedInvoice,
      editingId,
      defaultCurrency,
      expandedYears,
      groupedInvoices,
      yearTotals,
      quarters,
      addCompany,
      addItem,
      removeItem,
      submitInvoice,
      cancelEdit,
      editInvoice,
      deleteInvoice,
      viewInvoice,
      getCompanyName,
      toggleYear
    };
  }
});