<template>
  <div class="dashboard">
    <h1>Accounting Dashboard</h1>

    <section class="invoice-form">
      <h2>{{ editingId ? 'Edit Invoice' : 'Create Invoice' }}</h2>
      <form @submit.prevent="submitInvoice">
        <label>Datum opravljene storitve</label>
        <input type="date" v-model="form.date" required />

        <label>Valuta</label>
        <input type="text" v-model="form.currency" placeholder="e.g. EUR" required />

        <label>Stranka</label>
        <div class="stranka-select">
          <select v-model="form.companyId" required>
            <option value="">Select Company</option>
            <option v-for="company in companies" :key="company.id" :value="company.id">{{ company.name }}</option>
          </select>
          <button type="button" @click="showNewCompanyModal = true">Add New</button>
        </div>

        <label>Postavke</label>
        <div v-for="(item, index) in form.items" :key="index" class="item">
          <input type="text" v-model="item.title" placeholder="Title" required />
          <input type="number" v-model="item.value" placeholder="Value" required />
          <button type="button" @click="removeItem(index)">Remove</button>
        </div>
        <button type="button" @click="addItem">Add Item</button>

        <button type="submit">{{ editingId ? 'Update' : 'Create' }}</button>
        <button type="button" v-if="editingId" @click="cancelEdit">Cancel</button>
      </form>
    </section>

    <section class="invoice-list">
      <h2>Invoices</h2>
      <div v-for="(year, yearKey) in groupedInvoices" :key="yearKey">
        <h3 @click="toggleYear(yearKey)" style="cursor: pointer;">
          {{ yearKey }} (Total: {{ yearTotals[yearKey] }} {{ defaultCurrency }})
          <span>{{ expandedYears.has(yearKey) ? '-' : '+' }}</span>
        </h3>
        <div v-if="expandedYears.has(yearKey)">
          <div v-for="invoice in year" :key="invoice.id" class="invoice-item">
            <span>{{ invoice.date }} - {{ getCompanyName(invoice.companyId) }} - {{ invoice.total }} {{ invoice.currency }}</span>
            <button @click="viewInvoice(invoice)">View</button>
            <button @click="editInvoice(invoice)">Edit</button>
            <button @click="deleteInvoice(invoice.id)">Delete</button>
          </div>
        </div>
      </div>
    </section>

    <section class="tax-overview">
      <h2>Taxes Owed (Quarterly)</h2>
      <div v-for="(quarter, q) in quarters" :key="q">
        <h3>{{ q }}</h3>
        <p>Total: {{ quarter.total }} {{ defaultCurrency }}</p>
        <p>Tax (22%): {{ quarter.tax }} {{ defaultCurrency }}</p>
      </div>
    </section>

    <!-- New Company Modal -->
    <div v-if="showNewCompanyModal" class="modal">
      <div class="modal-content">
        <h2>Add New Company</h2>
        <form @submit.prevent="addCompany">
          <input type="text" v-model="newCompany.name" placeholder="Name" required />
          <input type="text" v-model="newCompany.address" placeholder="Address" required />
          <input type="text" v-model="newCompany.vatId" placeholder="VAT ID" required />
          <button type="submit">Add</button>
          <button type="button" @click="showNewCompanyModal = false">Cancel</button>
        </form>
      </div>
    </div>

    <!-- View Invoice Modal -->
    <div v-if="selectedInvoice" class="modal">
      <div class="modal-content">
        <h2>Invoice Details</h2>
        <p>Date: {{ selectedInvoice.date }}</p>
        <p>Currency: {{ selectedInvoice.currency }}</p>
        <p>Company: {{ getCompanyName(selectedInvoice.companyId) }}</p>
        <h3>Items:</h3>
        <ul>
          <li v-for="item in selectedInvoice.items" :key="item.title">{{ item.title }}: {{ item.value }}</li>
        </ul>
        <p>Total: {{ selectedInvoice.total }} {{ selectedInvoice.currency }}</p>
        <button @click="selectedInvoice = null">Close</button>
      </div>
    </div>
  </div>
</template>

<script src="./Dashboard.script.ts" lang="ts"></script>
<style src="./Dashboard.style.scss" lang="scss"></style>