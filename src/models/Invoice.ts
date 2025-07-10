import { InvoiceItem } from './InvoiceItem';

export class Invoice {
  id: string;
  date: string;
  currency: string;
  companyId: string;
  items: InvoiceItem[];
  createdAt: Date;

  constructor(id: string, date: string, currency: string, companyId: string, items: InvoiceItem[], createdAt = new Date()) {
    this.id = id;
    this.date = date;
    this.currency = currency;
    this.companyId = companyId;
    this.items = items;
    this.createdAt = createdAt;
  }

  get total() {
    return this.items.reduce((sum, item) => sum + item.value, 0);
  }
}