import React, { useState, useEffect } from 'react';
import { 
  TrendingUp, 
  TrendingDown, 
  DollarSign, 
  PlusCircle, 
  Camera, 
  CheckCircle2, 
  FileText, 
  Building2, 
  Mic, 
  ArrowLeft 
} from 'lucide-react';

const API_BASE_URL = '';

export default function App() {
  const [activeTab, setActiveTab] = useState('dashboard');
  const [type, setType] = useState('income');
  const [amount, setAmount] = useState('');
  const [category, setCategory] = useState('Daily Sales');
  const [loading, setLoading] = useState(false);
  
  const [data, setData] = useState({
    businessName: "BizTrack Merchant",
    totalIncome: 0,
    totalExpense: 0,
    netProfit: 0,
    score: 50,
    grade: "Fair",
    estimatedLoanRange: "$500 - $1,000",
    checklist: []
  });

  // ទាញយកទិន្នន័យសង្ខេបពី Backend ពេលបើក App ដំបូង
  const fetchSummary = async () => {
    try {
      const res = await fetch(`${API_BASE_URL}/api/summary`);
      const result = await res.json();
      setData(result);
    } catch (err) {
      console.error("Failed to connect to Backend API:", err);
    }
  };

  useEffect(() => {
    if (window.Telegram?.WebApp) {
      window.Telegram.WebApp.ready();
      window.Telegram.WebApp.expand();
    }
    fetchSummary();
  }, []);

  // បញ្ជូនទិន្នន័យចំណូល-ចំណាយទៅកាន់ Backend POST /api/transactions
  const handleAddTransaction = async (e) => {
    e.preventDefault();
    const val = parseFloat(amount);
    if (!val || val <= 0) return;

    setLoading(true);
    try {
      const res = await fetch(`${API_BASE_URL}/api/transactions`, {
        method: 'POST',
        headers: { 'Content-Type': 'application/json' },
        body: JSON.stringify({
          type,
          amount: val,
          category
        })
      });

      const responseData = await res.json();
      if (responseData.updatedMetrics) {
        setData(responseData.updatedMetrics);
      } else {
        await fetchSummary();
      }

      setAmount('');
      setActiveTab('dashboard');

      if (window.Telegram?.WebApp?.HapticFeedback) {
        window.Telegram.WebApp.HapticFeedback.notificationOccurred('success');
      }
    } catch (err) {
      alert("Error sending transaction to server!");
      console.error(err);
    } finally {
      setLoading(false);
    }
  };

  return (
    <div className="max-w-md mx-auto min-h-screen bg-slate-900 text-slate-100 flex flex-col font-sans pb-16">
      {/* Header */}
      <div className="p-4 border-b border-slate-800 flex justify-between items-center bg-slate-900/90 sticky top-0 z-10 backdrop-blur">
        <div>
          <div className="flex items-center gap-2">
            <span className="w-2.5 h-2.5 rounded-full bg-emerald-500 animate-pulse"></span>
            <h1 className="font-bold text-lg tracking-wide text-white">BizTrack</h1>
          </div>
          <p className="text-xs text-slate-400">{data.businessName} • Phnom Penh</p>
        </div>
        <span className="text-xs bg-indigo-500/20 text-indigo-300 px-2.5 py-1 rounded-full border border-indigo-500/30">
          CBC-Connected
        </span>
      </div>

      {/* Main Content */}
      <div className="p-4 flex-1">
        {activeTab === 'dashboard' && (
          <div className="space-y-4">
            {/* Net Profit Banner */}
            <div className="bg-gradient-to-br from-indigo-900/60 to-slate-800 p-4 rounded-2xl border border-indigo-500/30 shadow-lg">
              <p className="text-xs text-indigo-300 font-medium">ប្រាក់ចំណេញសុទ្ធខែនេះ (Net Profit)</p>
              <h2 className="text-3xl font-extrabold text-white mt-1">
                {data.netProfit >= 0 ? `+$${data.netProfit}` : `-$${Math.abs(data.netProfit)}`}
              </h2>
              <div className="flex gap-4 mt-3 pt-3 border-t border-slate-700/60">
                <div>
                  <span className="text-[11px] text-slate-400 flex items-center gap-1">
                    <TrendingUp className="w-3 h-3 text-emerald-400" /> ចំណូល (Income)
                  </span>
                  <p className="text-sm font-semibold text-emerald-400">${data.totalIncome}</p>
                </div>
                <div>
                  <span className="text-[11px] text-slate-400 flex items-center gap-1">
                    <TrendingDown className="w-3 h-3 text-rose-400" /> ចំណាយ (Expense)
                  </span>
                  <p className="text-sm font-semibold text-rose-400">${data.totalExpense}</p>
                </div>
              </div>
            </div>

            {/* Loan Readiness Score Card */}
            <div 
              onClick={() => setActiveTab('score')}
              className="bg-slate-800/80 p-4 rounded-2xl border border-slate-700 flex items-center justify-between cursor-pointer hover:border-emerald-500/50 transition"
            >
              <div className="flex items-center gap-3">
                <div className="w-12 h-12 rounded-xl bg-emerald-500/20 border border-emerald-500/40 flex items-center justify-center text-emerald-400 font-black text-lg">
                  {data.score}
                </div>
                <div>
                  <h3 className="font-semibold text-sm text-white">កម្រិតពិន្ទុកម្ចី (CBC-Ready Score)</h3>
                  <p className="text-xs text-emerald-400 font-medium mt-0.5">លទ្ធភាពខ្ចី: {data.estimatedLoanRange}</p>
                </div>
              </div>
              <span className="text-slate-400 text-xs">លម្អិត &rarr;</span>
            </div>

            {/* Quick Action Grid */}
            <div className="grid grid-cols-2 gap-3 pt-2">
              <button 
                onClick={() => setActiveTab('record')}
                className="bg-emerald-600 hover:bg-emerald-500 text-white font-medium p-3.5 rounded-xl flex items-center justify-center gap-2 text-sm shadow-lg shadow-emerald-900/30 active:scale-95 transition"
              >
                <PlusCircle className="w-4 h-4" /> កត់ត្រារហ័ស
              </button>
              <button 
                onClick={() => alert("AI Scan Receipt: Simulated scanning receipt... (+$15 logged)")}
                className="bg-slate-800 hover:bg-slate-700 text-slate-200 font-medium p-3.5 rounded-xl border border-slate-700 flex items-center justify-center gap-2 text-sm active:scale-95 transition"
              >
                <Camera className="w-4 h-4 text-indigo-400" /> ស្កេនវិក្កយបត្រ
              </button>
            </div>
          </div>
        )}

        {activeTab === 'record' && (
          <div className="space-y-4">
            <button 
              onClick={() => setActiveTab('dashboard')}
              className="text-xs text-slate-400 flex items-center gap-1 hover:text-white"
            >
              <ArrowLeft className="w-3.5 h-3.5" /> ត្រឡប់ទៅផ្ទាំងដើម
            </button>

            <form onSubmit={handleAddTransaction} className="bg-slate-800/90 p-4 rounded-2xl border border-slate-700 space-y-4">
              <h3 className="font-bold text-sm text-white border-b border-slate-700 pb-2">កត់ត្រាចំណូល-ចំណាយប្រចាំថ្ងៃ</h3>

              <div className="grid grid-cols-2 gap-2 bg-slate-900 p-1 rounded-xl">
                <button
                  type="button"
                  onClick={() => setType('income')}
                  className={`py-2 text-xs font-semibold rounded-lg transition ${type === 'income' ? 'bg-emerald-600 text-white' : 'text-slate-400'}`}
                >
                  + ចំណូល (Income)
                </button>
                <button
                  type="button"
                  onClick={() => setType('expense')}
                  className={`py-2 text-xs font-semibold rounded-lg transition ${type === 'expense' ? 'bg-rose-600 text-white' : 'text-slate-400'}`}
                >
                  - ចំណាយ (Expense)
                </button>
              </div>

              <div>
                <label className="text-xs text-slate-400 block mb-1">ចំនួនទឹកប្រាក់ ($ USD)</label>
                <div className="relative">
                  <DollarSign className="w-4 h-4 absolute left-3 top-3 text-slate-500" />
                  <input 
                    type="number" 
                    step="any"
                    required
                    value={amount}
                    onChange={(e) => setAmount(e.target.value)}
                    placeholder="0.00"
                    className="w-full bg-slate-900 border border-slate-700 rounded-xl py-2.5 pl-9 pr-4 text-white text-sm focus:outline-none focus:border-indigo-500"
                  />
                </div>
              </div>

              <div>
                <label className="text-xs text-slate-400 block mb-1">ប្រភេទប្រតិបត្តិការ</label>
                <select 
                  value={category} 
                  onChange={(e) => setCategory(e.target.value)}
                  className="w-full bg-slate-900 border border-slate-700 rounded-xl py-2.5 px-3 text-white text-sm focus:outline-none focus:border-indigo-500"
                >
                  <option value="Daily Sales">លក់កាហ្វេប្រចាំថ្ងៃ (Daily Sales)</option>
                  <option value="Raw Materials">ទិញគ្រាប់កាហ្វេ/ទឹកដោះគោ (Inventory)</option>
                  <option value="Shop Rent">ថ្លៃជួលតូប (Rent)</option>
                  <option value="Utilities">ថ្លៃទឹកភ្លើង (Utilities)</option>
                </select>
              </div>

              <div className="p-3 bg-slate-900/60 rounded-xl border border-dashed border-slate-700 flex items-center justify-between text-slate-400">
                <span className="text-xs">🎙 ជំនួយការសំឡេងខ្មែរ (AI Voice)</span>
                <button type="button" onClick={() => alert("សាកល្បងសម្គាល់សំឡេង៖ 'លក់កាហ្វេបាន ២៥ ដុល្លារ'")} className="p-2 bg-indigo-600 text-white rounded-full">
                  <Mic className="w-3.5 h-3.5" />
                </button>
              </div>

              <button 
                type="submit"
                disabled={loading}
                className="w-full bg-indigo-600 hover:bg-indigo-500 text-white font-semibold py-3 rounded-xl text-sm transition disabled:opacity-50"
              >
                {loading ? 'កំពុងរក្សាទុក...' : 'រក្សាទុកទិន្នន័យ (Save Entry)'}
              </button>
            </form>
          </div>
        )}

        {activeTab === 'score' && (
          <div className="space-y-4">
            <button 
              onClick={() => setActiveTab('dashboard')}
              className="text-xs text-slate-400 flex items-center gap-1 hover:text-white"
            >
              <ArrowLeft className="w-3.5 h-3.5" /> ត្រឡប់ក្រោយ
            </button>

            <div className="bg-slate-800 p-5 rounded-2xl border border-slate-700 text-center">
              <p className="text-xs text-slate-400">ការវាយតម្លៃឥណទានបឋម (CBC Metric Benchmark)</p>
              <div className="my-4 inline-flex items-center justify-center w-28 h-28 rounded-full border-4 border-emerald-500/30 bg-emerald-500/10">
                <div>
                  <span className="text-4xl font-black text-emerald-400">{data.score}</span>
                  <span className="text-[10px] block text-slate-400 uppercase tracking-wider">/ 100</span>
                </div>
              </div>
              <h4 className="font-bold text-white text-base">ស្ថានភាព៖ {data.grade || "រឹងមាំ"}</h4>
              <p className="text-xs text-slate-400 mt-1">ទិន្នន័យចរន្តសាច់ប្រាក់គ្រប់គ្រាន់សម្រាប់ធនាគារពិចារណា</p>
            </div>

            <div className="bg-slate-800/60 p-4 rounded-xl border border-slate-700 space-y-2.5">
              {(data.checklist || []).map((item, index) => (
                <div key={index} className="flex items-center gap-2.5 text-xs text-slate-200">
                  <CheckCircle2 className={`w-4 h-4 shrink-0 ${item.passed ? 'text-emerald-400' : 'text-slate-500'}`} />
                  <span>{item.label}</span>
                </div>
              ))}
            </div>

            <button 
              onClick={async () => {
                const res = await fetch(`${API_BASE_URL}/api/report/export`);
                const report = await res.json();
                alert(`របាយការណ៍ហិរញ្ញវត្ថុផ្លូវការបានបង្កើតជោគជ័យ!\nចំណងជើង: ${report.reportTitle}\nកាលបរិច្ឆេទ: ${report.generatedDate}`);
              }}
              className="w-full bg-slate-800 hover:bg-slate-700 border border-slate-700 text-slate-200 font-medium py-3 rounded-xl flex items-center justify-center gap-2 text-xs transition"
            >
              <FileText className="w-4 h-4 text-indigo-400" /> ទាញយករបាយការណ៍ហិរញ្ញវត្ថុជា PDF
            </button>
          </div>
        )}
      </div>

      {/* Bottom Navigation */}
      <div className="fixed bottom-0 left-0 right-0 max-w-md mx-auto bg-slate-900 border-t border-slate-800 flex justify-around p-2 text-xs">
        <button 
          onClick={() => setActiveTab('dashboard')} 
          className={`flex flex-col items-center gap-1 ${activeTab === 'dashboard' ? 'text-indigo-400' : 'text-slate-500'}`}
        >
          <TrendingUp className="w-4 h-4" /> ផ្ទាំងដើម
        </button>
        <button 
          onClick={() => setActiveTab('record')} 
          className={`flex flex-col items-center gap-1 ${activeTab === 'record' ? 'text-indigo-400' : 'text-slate-500'}`}
        >
          <PlusCircle className="w-4 h-4" /> កត់ត្រា
        </button>
        <button 
          onClick={() => setActiveTab('score')} 
          className={`flex flex-col items-center gap-1 ${activeTab === 'score' ? 'text-indigo-400' : 'text-slate-500'}`}
        >
          <Building2 className="w-4 h-4" /> ពិន្ទុកម្ចី
        </button>
      </div>
    </div>
  );
}