<?php
session_start();
require_once 'config/config.php';
require_once 'includes/auth.php';

$page_title = "Financial Calculators";
include 'includes/header.php';
?>

<div class="container">
    <h1 class="mb-4">Financial Calculators</h1>
    
    <div class="row">
        <!-- Income Tax Calculator -->
        <div class="col-md-6 mb-4">
            <div class="card calculator-card">
                <div class="card-body">
                    <h5 class="card-title">
                        <i class="fas fa-calculator"></i> Income Tax Calculator
                    </h5>
                    <form id="taxCalculatorForm" class="calculator-form">
                        <div class="mb-3">
                            <label for="annualIncome" class="form-label">Annual Income ($)</label>
                            <input type="number" class="form-control" id="annualIncome" required>
                        </div>
                        <div class="mb-3">
                            <label for="deductions" class="form-label">Total Deductions ($)</label>
                            <input type="number" class="form-control" id="deductions" value="0">
                        </div>
                        <button type="submit" class="btn btn-primary">Calculate Tax</button>
                    </form>
                    <div id="taxResult" class="mt-3 result-box" style="display: none;">
                        <h6>Tax Breakdown:</h6>
                        <div class="result-content"></div>
                    </div>
                </div>
            </div>
        </div>

        <!-- Investment Return Calculator -->
        <div class="col-md-6 mb-4">
            <div class="card calculator-card">
                <div class="card-body">
                    <h5 class="card-title">
                        <i class="fas fa-chart-line"></i> Investment Return Calculator
                    </h5>
                    <form id="investmentCalculatorForm" class="calculator-form">
                        <div class="mb-3">
                            <label for="initialInvestment" class="form-label">Initial Investment ($)</label>
                            <input type="number" class="form-control" id="initialInvestment" required>
                        </div>
                        <div class="mb-3">
                            <label for="monthlyContribution" class="form-label">Monthly Contribution ($)</label>
                            <input type="number" class="form-control" id="monthlyContribution" value="0">
                        </div>
                        <div class="mb-3">
                            <label for="annualReturn" class="form-label">Expected Annual Return (%)</label>
                            <input type="number" class="form-control" id="annualReturn" required>
                        </div>
                        <div class="mb-3">
                            <label for="investmentYears" class="form-label">Investment Period (Years)</label>
                            <input type="number" class="form-control" id="investmentYears" required>
                        </div>
                        <button type="submit" class="btn btn-primary">Calculate Returns</button>
                    </form>
                    <div id="investmentResult" class="mt-3 result-box" style="display: none;">
                        <h6>Investment Summary:</h6>
                        <div class="result-content"></div>
                    </div>
                </div>
            </div>
        </div>

        <!-- Retirement Planning Calculator -->
        <div class="col-md-6 mb-4">
            <div class="card calculator-card">
                <div class="card-body">
                    <h5 class="card-title">
                        <i class="fas fa-umbrella-beach"></i> Retirement Planning Calculator
                    </h5>
                    <form id="retirementCalculatorForm" class="calculator-form">
                        <div class="mb-3">
                            <label for="currentAge" class="form-label">Current Age</label>
                            <input type="number" class="form-control" id="currentAge" required>
                        </div>
                        <div class="mb-3">
                            <label for="retirementAge" class="form-label">Retirement Age</label>
                            <input type="number" class="form-control" id="retirementAge" required>
                        </div>
                        <div class="mb-3">
                            <label for="monthlyIncome" class="form-label">Desired Monthly Income ($)</label>
                            <input type="number" class="form-control" id="monthlyIncome" required>
                        </div>
                        <div class="mb-3">
                            <label for="currentSavings" class="form-label">Current Savings ($)</label>
                            <input type="number" class="form-control" id="currentSavings" required>
                        </div>
                        <button type="submit" class="btn btn-primary">Calculate Retirement Plan</button>
                    </form>
                    <div id="retirementResult" class="mt-3 result-box" style="display: none;">
                        <h6>Retirement Analysis:</h6>
                        <div class="result-content"></div>
                    </div>
                </div>
            </div>
        </div>

        <!-- Loan Calculator -->
        <div class="col-md-6 mb-4">
            <div class="card calculator-card">
                <div class="card-body">
                    <h5 class="card-title">
                        <i class="fas fa-money-bill-wave"></i> Loan Calculator
                    </h5>
                    <form id="loanCalculatorForm" class="calculator-form">
                        <div class="mb-3">
                            <label for="loanAmount" class="form-label">Loan Amount ($)</label>
                            <input type="number" class="form-control" id="loanAmount" required>
                        </div>
                        <div class="mb-3">
                            <label for="interestRate" class="form-label">Annual Interest Rate (%)</label>
                            <input type="number" class="form-control" id="interestRate" required>
                        </div>
                        <div class="mb-3">
                            <label for="loanTerm" class="form-label">Loan Term (Years)</label>
                            <input type="number" class="form-control" id="loanTerm" required>
                        </div>
                        <button type="submit" class="btn btn-primary">Calculate EMI</button>
                    </form>
                    <div id="loanResult" class="mt-3 result-box" style="display: none;">
                        <h6>Loan Analysis:</h6>
                        <div class="result-content"></div>
                    </div>
                </div>
            </div>
        </div>
    </div>
</div>

<style>
.calculator-card {
    height: 100%;
    transition: transform 0.2s;
}

.calculator-card:hover {
    transform: translateY(-5px);
}

.calculator-form {
    margin-top: 1rem;
}

.result-box {
    background-color: #f8f9fa;
    padding: 1rem;
    border-radius: 4px;
    margin-top: 1rem;
}

.result-content {
    font-size: 0.9rem;
}

.card-title {
    color: #0d6efd;
    display: flex;
    align-items: center;
    gap: 0.5rem;
}

.card-title i {
    font-size: 1.2rem;
}
</style>

<script>
document.addEventListener('DOMContentLoaded', function() {
    // Income Tax Calculator
    document.getElementById('taxCalculatorForm').addEventListener('submit', function(e) {
        e.preventDefault();
        const income = parseFloat(document.getElementById('annualIncome').value);
        const deductions = parseFloat(document.getElementById('deductions').value);
        const taxableIncome = income - deductions;
        
        // Simple tax calculation (example rates)
        let tax = 0;
        if (taxableIncome <= 50000) {
            tax = taxableIncome * 0.1;
        } else if (taxableIncome <= 100000) {
            tax = 5000 + (taxableIncome - 50000) * 0.2;
        } else {
            tax = 15000 + (taxableIncome - 100000) * 0.3;
        }
        
        const resultBox = document.getElementById('taxResult');
        resultBox.style.display = 'block';
        resultBox.querySelector('.result-content').innerHTML = `
            <p>Taxable Income: $${taxableIncome.toFixed(2)}</p>
            <p>Estimated Tax: $${tax.toFixed(2)}</p>
            <p>Effective Tax Rate: ${((tax/taxableIncome)*100).toFixed(2)}%</p>
        `;
    });

    // Investment Return Calculator
    document.getElementById('investmentCalculatorForm').addEventListener('submit', function(e) {
        e.preventDefault();
        const initial = parseFloat(document.getElementById('initialInvestment').value);
        const monthly = parseFloat(document.getElementById('monthlyContribution').value);
        const rate = parseFloat(document.getElementById('annualReturn').value) / 100;
        const years = parseInt(document.getElementById('investmentYears').value);
        
        let futureValue = initial;
        for (let i = 0; i < years; i++) {
            futureValue = (futureValue + monthly * 12) * (1 + rate);
        }
        
        const totalContributions = initial + (monthly * 12 * years);
        const interestEarned = futureValue - totalContributions;
        
        const resultBox = document.getElementById('investmentResult');
        resultBox.style.display = 'block';
        resultBox.querySelector('.result-content').innerHTML = `
            <p>Future Value: $${futureValue.toFixed(2)}</p>
            <p>Total Contributions: $${totalContributions.toFixed(2)}</p>
            <p>Interest Earned: $${interestEarned.toFixed(2)}</p>
        `;
    });

    // Retirement Planning Calculator
    document.getElementById('retirementCalculatorForm').addEventListener('submit', function(e) {
        e.preventDefault();
        const currentAge = parseInt(document.getElementById('currentAge').value);
        const retirementAge = parseInt(document.getElementById('retirementAge').value);
        const monthlyIncome = parseFloat(document.getElementById('monthlyIncome').value);
        const currentSavings = parseFloat(document.getElementById('currentSavings').value);
        
        const yearsToRetirement = retirementAge - currentAge;
        const annualIncomeNeeded = monthlyIncome * 12;
        const totalNeeded = annualIncomeNeeded * 25; // 25 years of retirement
        const monthlySavingsNeeded = (totalNeeded - currentSavings) / (yearsToRetirement * 12);
        
        const resultBox = document.getElementById('retirementResult');
        resultBox.style.display = 'block';
        resultBox.querySelector('.result-content').innerHTML = `
            <p>Years to Retirement: ${yearsToRetirement}</p>
            <p>Total Savings Needed: $${totalNeeded.toFixed(2)}</p>
            <p>Monthly Savings Required: $${monthlySavingsNeeded.toFixed(2)}</p>
        `;
    });

    // Loan Calculator
    document.getElementById('loanCalculatorForm').addEventListener('submit', function(e) {
        e.preventDefault();
        const principal = parseFloat(document.getElementById('loanAmount').value);
        const rate = parseFloat(document.getElementById('interestRate').value) / 100 / 12;
        const term = parseInt(document.getElementById('loanTerm').value) * 12;
        
        const emi = principal * rate * Math.pow(1 + rate, term) / (Math.pow(1 + rate, term) - 1);
        const totalPayment = emi * term;
        const totalInterest = totalPayment - principal;
        
        const resultBox = document.getElementById('loanResult');
        resultBox.style.display = 'block';
        resultBox.querySelector('.result-content').innerHTML = `
            <p>Monthly EMI: $${emi.toFixed(2)}</p>
            <p>Total Payment: $${totalPayment.toFixed(2)}</p>
            <p>Total Interest: $${totalInterest.toFixed(2)}</p>
        `;
    });
});
</script>

<?php include 'includes/footer.php'; ?> 