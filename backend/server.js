const express = require('express');
const cors = require('cors');

const app = express();
app.use(cors());
app.use(express.json());

// In-Memory Database សម្រាប់ Demo & Initial State
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

// Helper: គណនាផលបូក និងពិន្ទុឥណទាន
function calculateMetrics() {
  const totalIncome = transactions
    .filter(t => t.type === 'income')
    .reduce((sum, t) => sum + t.amount, 0);

  const totalExpense = transactions
    .filter(t => t.type === 'expense')
    .reduce((sum, t) => sum + t.amount, 0);

  const netProfit = totalIncome - totalExpense;
  const margin = totalIncome > 0 ? (netProfit / totalIncome) * 100 : 0;

  // ក្បួនគណនា Alternative Credit Score (0 - 100)
  let score = 50; // Base score
  if (transactions.length >= 5) score += 15; // ភាពទៀងទាត់នៃការកត់ត្រា
  if (netProfit > 0) score += 20;             // លំហូរសាច់ប្រាក់វិជ្ជមាន
  if (margin > 30) score += 15;               // អត្រាប្រាក់ចំណេញរឹងមាំ

  score = Math.min(100, Math.max(0, score));

  // ប៉ាន់ស្មានកម្ចីខ្នាតតូចគ្មានទ្រព្យបញ្ចាំ (Unsecured Micro-loan)
  const estimatedMaxLoan = Math.round(netProfit * 3.5);
  const loanMin = 500;
  const loanMax = Math.max(1000, estimatedMaxLoan);

  return {
    businessName: business.name,
    totalIncome,
    totalExpense,
    netProfit,
    margin: `${margin.toFixed(1)}%`,
    score,
    grade: score >= 75 ? "Excellent (CBC-Ready)" : "Fair",
    estimatedLoanRange: `$${loanMin} - $${loanMax.toLocaleString()}`,
    checklist: [
      { label: "Daily Entry Consistency", passed: transactions.length >= 5 },
      { label: "Positive Net Cashflow", passed: netProfit > 0 },
      { label: "Zero Unreported Overdue", passed: true }
    ]
  };
}

// 0. Base Route (ការពារកុំឱ្យចេញ Cannot GET /)
app.get('/', (req, res) => {
  res.send('🚀 BizTrack API Server is running smoothly!');
});

// 1. បញ្ចូលប្រតិបត្តិការថ្មី (Smart Entry)
app.post('/api/transactions', (req, res) => {
  const { type, amount, category } = req.body;
  
  if (!type || amount === undefined || isNaN(parseFloat(amount))) {
    return res.status(400).json({ error: "Invalid or missing amount/type" });
  }

  const newTx = {
    id: Date.now(),
    type,
    amount: parseFloat(amount),
    category: category || (type === 'income' ? 'Daily Sales' : 'Expense'),
    date: new Date().toISOString().split('T')[0]
  };

  transactions.unshift(newTx);
  const metrics = calculateMetrics();

  res.status(201).json({
    message: "Transaction recorded successfully",
    entry: newTx,
    updatedMetrics: metrics
  });
});

// 2. ទាញយកទិន្នន័យសង្ខេបសម្រាប់ Dashboard (/api/summary)
app.get('/api/summary', (req, res) => {
  const metrics = calculateMetrics();
  res.json(metrics);
});

// 3. ទាញយកព័ត៌មានលម្អិតអំពីពិន្ទុឥណទាន (/api/score)
app.get('/api/score', (req, res) => {
  const metrics = calculateMetrics();
  res.json({
    ...metrics,
    transactionCount: transactions.length,
    recentTransactions: transactions.slice(0, 5)
  });
});

// 4. ទាញយកទិន្នន័យសម្រាប់បង្កើតរបាយការណ៍ផ្លូវការ (PDF Export Simulation)
app.get('/api/report/export', (req, res) => {
  const metrics = calculateMetrics();
  res.json({
    reportTitle: "MSME CASHFLOW & CREDIT-READINESS STATEMENT",
    generatedDate: new Date().toLocaleDateString('en-US', { year: 'numeric', month: 'long', day: 'numeric' }),
    partnerVerification: "Prepared for CBC / Partner Bank Review",
    businessProfile: business,
    financialSummary: metrics,
    transactionsAudit: transactions
  });
});

const PORT = 5000;
app.listen(PORT, () => {
  console.log(`✅ BizTrack API running on http://localhost:${PORT}`);
});