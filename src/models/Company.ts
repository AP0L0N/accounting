export class Company {
  id: string;
  name: string;
  address: string;
  vatId: string;

  constructor(id: string, name: string, address: string, vatId: string) {
    this.id = id;
    this.name = name;
    this.address = address;
    this.vatId = vatId;
  }
}