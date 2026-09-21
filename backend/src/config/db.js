// កន្លែងរក្សាទុកទិន្នន័យ In-memory សម្រាប់ Demo
let business = {
  id: "biz_001",
  name: "Cafe Sok Sabay",
  owner: "Sokha",
  category: "Coffee Stall",
  location: "Phnom Penh",
  joinedAt: "2026-06-01"
};

let transactions = [
  { id: 1, type: "income", amount: 65, category: "Morning Sales", date: "2026-09-15" },
  { id: 2, type: "expense", amount: 20, category: "Milk & Beans", date: "2026-09-15" },
  { id: 3, type: "income", amount: 75, category: "Afternoon Sales", date: "2026-09-16" },
  { id: 4, type: "expense", amount: 15, category: "Cups & Ice", date: "2026-09-16" },
  { id: 5, type: "income", amount: 80, category: "Daily Sales", date: "2026-09-17" },
  { id: 6, type: "expense", amount: 25, category: "Coffee Powder", date: "2026-09-17" }
];

module.exports = { business, transactions };