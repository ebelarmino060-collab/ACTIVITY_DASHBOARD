import React, { useEffect, useMemo, useState } from "react";
import { api_v1 } from "../../api/axios";
import "./styles/dashboard.css";


// Format currency
const currency = (value) =>
  new Intl.NumberFormat("en-PH", {
    style: "currency",
    currency: "PHP",
    maximumFractionDigits: 2,
  }).format(Number(value) || 0);

// Format numbers
const number = (value) =>
  new Intl.NumberFormat("en-PH").format(Number(value) || 0);

// Handle Laravel response arrays
function unwrapList(value) {
  if (Array.isArray(value)) return value;
  if (Array.isArray(value?.data)) return value.data;
  return [];
}

// Get category name
function getProductCategory(product) {
  if (typeof product?.category === "string") {
    return product.category;
  }

  return product?.category?.name || "Uncategorized";
}

export default function Dashboard() {
  const [queryData, setQueryData] = useState(null);
  const [products, setProducts] = useState([]);
  const [pagination, setPagination] = useState(null);

  const [loading, setLoading] = useState(true);
  const [error, setError] = useState("");

  const [search, setSearch] = useState("");
  const [activeFilter, setActiveFilter] = useState("all");

  // Fetch Laravel API data
  const loadDashboard = async () => {
    try {
      setLoading(true);
      setError("");

      const [queryResponse, productsResponse] = await Promise.all([
        api_v1.get("/product/queries"),
        api_v1.get("/products"),
      ]);

      setQueryData(queryResponse.data?.data || {});

      // ProductResource nests fields under "attributes"
      const productRows = unwrapList(productsResponse.data).map(
        (item) => ({ id: item.id, ...item.attributes })
      );

      setProducts(productRows);

      const meta = productsResponse.data?.meta;

      setPagination(
        meta
          ? {
              currentPage: meta.current_page,
              lastPage: meta.last_page,
              total: meta.total,
            }
          : null
      );
    } catch (err) {
      console.error("Dashboard API Error:", err);

      setError(
        err.response?.data?.message ||
          "Unable to load dashboard data. Check your Laravel API connection."
      );
    } finally {
      setLoading(false);
    }
  };

  useEffect(() => {
    loadDashboard();
  }, []);

  // Search and filter products
  const filteredProducts = useMemo(() => {
    return products.filter((product) => {
      const matchesSearch = String(product.name || "")
        .toLowerCase()
        .includes(search.toLowerCase());

      const matchesFilter =
        activeFilter === "all" ||
        getProductCategory(product).toLowerCase() === activeFilter;

      return matchesSearch && matchesFilter;
    });
  }, [products, search, activeFilter]);

  // Laravel query results
  const categoryTotals = unwrapList(
    queryData?.total_price_per_category
  );

  const categoryAverages = unwrapList(
    queryData?.average_price_per_category
  );

  const sortedAverages = unwrapList(
    queryData?.average_price_sorted
  );

  const electronics = unwrapList(
    queryData?.electronics
  );

  const firstFive = unwrapList(
    queryData?.first_five_products
  );

  const orderedProducts = unwrapList(
    queryData?.products_ordered_by_price
  );

  const maxCategoryTotal = Math.max(
    ...categoryTotals.map(
      (item) => Number(item.total_price) || 0
    ),
    1
  );

  // Dashboard summary cards
  const stats = [
    {
      label: "Total Products",
      value: number(pagination?.total ?? products.length),
      icon: "▦",
      description: "Products in your catalog",
      tone: "blue",
    },
    {
      label: "Total Product Value",
      value: currency(queryData?.total_price),
      icon: "₱",
      description: "Sum of all product prices",
      tone: "purple",
    },
    {
      label: "Average Price",
      value: currency(queryData?.average_price),
      icon: "↗",
      description: "Average price per product",
      tone: "green",
    },
    {
      label: "Highest Price",
      value: currency(queryData?.highest_price),
      icon: "↑",
      description: "Most expensive product",
      tone: "orange",
    },
    {
      label: "Lowest Price",
      value: currency(queryData?.lowest_price),
      icon: "↓",
      description: "Lowest product price",
      tone: "pink",
    },
  ];

  // Loading screen
  if (loading) {
    return (
      <main className="dashboard-page">
        <div className="dashboard-loading">
          <span className="dashboard-spinner"></span>
          <p>Loading dashboard data...</p>
        </div>
      </main>
    );
  }

  return (
    <main className="dashboard-page">
      <div className="dashboard-container">

        {/* HEADER */}
        <header className="dashboard-header">
          <div>
            <p className="dashboard-eyebrow">
              OVERVIEW
            </p>

            <h1>Dashboard</h1>

            <p className="dashboard-subtitle">
              Monitor your products and review your inventory analytics.
            </p>
          </div>

          <button
            className="refresh-button"
            onClick={loadDashboard}
          >
            <span>↻</span> Refresh
          </button>
        </header>

        {/* ERROR MESSAGE */}
        {error && (
          <div className="dashboard-error">
            <strong>Connection Error</strong>
            <p>{error}</p>
            <small>API: {api_v1.defaults.baseURL}</small>
          </div>
        )}

        {/* SUMMARY CARDS */}
        <section className="stats-grid">
          {stats.map((stat) => (
            <article
              className="stat-card"
              key={stat.label}
            >
              <div className={`stat-icon ${stat.tone}`}>
                {stat.icon}
              </div>

              <div className="stat-label">
                {stat.label}
              </div>

              <div className="stat-value">
                {stat.value}
              </div>

              <div className="stat-description">
                {stat.description}
              </div>
            </article>
          ))}
        </section>

        {/* CATEGORY ANALYTICS */}
        <section className="dashboard-grid">

          {/* TOTAL PRICE PER CATEGORY */}
          <article className="dashboard-panel">
            <div className="panel-heading">
              <div>
                <h2>Product Value by Category</h2>
                <p>Total price grouped by category</p>
              </div>
            </div>

            {categoryTotals.length > 0 ? (
              <div className="category-chart">
                {categoryTotals.map((item) => {
                  const value =
                    Number(item.total_price) || 0;

                  const width =
                    (value / maxCategoryTotal) * 100;

                  return (
                    <div
                      className="category-bar-row"
                      key={item.name}
                    >
                      <div className="category-bar-info">
                        <span>{item.name}</span>
                        <strong>{currency(value)}</strong>
                      </div>

                      <div className="category-bar-track">
                        <div
                          className="category-bar-fill"
                          style={{ width: `${width}%` }}
                        ></div>
                      </div>
                    </div>
                  );
                })}
              </div>
            ) : (
              <p className="empty-state">
                No category data available.
              </p>
            )}
          </article>

          {/* AVERAGE PRICE PER CATEGORY */}
          <article className="dashboard-panel">
            <div className="panel-heading">
              <div>
                <h2>Category Averages</h2>
                <p>Average price per category</p>
              </div>
            </div>

            {categoryAverages.length > 0 ? (
              <div className="category-average-list">
                {categoryAverages.map((item) => (
                  <div
                    className="average-row"
                    key={item.name}
                  >
                    <div className="average-category">
                      <span className="category-dot"></span>
                      <span>{item.name}</span>
                    </div>

                    <strong>
                      {currency(item.avg_price)}
                    </strong>
                  </div>
                ))}
              </div>
            ) : (
              <p className="empty-state">
                No category averages available.
              </p>
            )}
          </article>

        </section>

        {/* PRODUCT TABLE */}
        <section className="dashboard-panel products-panel">

          <div className="panel-heading products-heading">
            <div>
              <h2>Products</h2>
              <p>
                Browse and filter products from your Laravel database.
              </p>
            </div>

            <span className="product-count">
              {number(filteredProducts.length)} shown
            </span>
          </div>

          {/* SEARCH AND FILTER */}
          <div className="product-toolbar">

            <div className="search-box">
              <span>⌕</span>

              <input
                type="search"
                placeholder="Search product name..."
                value={search}
                onChange={(e) => setSearch(e.target.value)}
              />
            </div>

            <select
              value={activeFilter}
              onChange={(e) =>
                setActiveFilter(e.target.value)
              }
            >
              <option value="all">
                All Categories
              </option>

              {[
                ...new Set(
                  products.map(getProductCategory)
                ),
              ].map((category) => (
                <option
                  key={category}
                  value={category.toLowerCase()}
                >
                  {category}
                </option>
              ))}
            </select>

          </div>

          {/* TABLE */}
          <div className="table-wrapper">
            <table className="dashboard-table">

              <thead>
                <tr>
                  <th>Product</th>
                  <th>Category</th>
                  <th>Price</th>
                </tr>
              </thead>

              <tbody>
                {filteredProducts.length > 0 ? (
                  filteredProducts.map((product) => (
                    <tr key={product.id}>

                      <td>
                        <div className="product-name-cell">
                          <span className="product-avatar">
                            {String(product.name || "P")
                              .charAt(0)
                              .toUpperCase()}
                          </span>

                          <div>
                            <strong>
                              {product.name}
                            </strong>

                            <small>
                              Product #{product.id}
                            </small>
                          </div>
                        </div>
                      </td>

                      <td>
                        {getProductCategory(product)}
                      </td>

                      <td className="price-cell">
                        {currency(product.price)}
                      </td>

                    </tr>
                  ))
                ) : (
                  <tr>
                    <td
                      colSpan="3"
                      className="table-empty"
                    >
                      No products found.
                    </td>
                  </tr>
                )}
              </tbody>

            </table>
          </div>

          {pagination && (
            <div className="table-footer">
              Showing page {pagination.currentPage || 1}
              {" "}of {pagination.lastPage || 1}
              {" "}· {number(pagination.total || 0)} total products
            </div>
          )}

        </section>

        {/* LOWER ANALYTICS */}
        <section className="dashboard-grid lower-grid">

          {/* ELECTRONICS */}
          <article className="dashboard-panel">
            <div className="panel-heading">
              <div>
                <h2>Electronics Products</h2>
                <p>
                  Products in Electronics category
                </p>
              </div>

              <span className="mini-count">
                {electronics.length}
              </span>
            </div>

            {electronics.length > 0 ? (
              <div className="compact-list">
                {electronics.slice(0, 6).map((product) => (
                  <div
                    className="compact-row"
                    key={product.id}
                  >
                    <div>
                      <strong>{product.name}</strong>
                      <small>Product #{product.id}</small>
                    </div>

                    <strong>
                      {currency(product.price)}
                    </strong>
                  </div>
                ))}
              </div>
            ) : (
              <p className="empty-state">
                No Electronics products found.
              </p>
            )}
          </article>

          {/* AVERAGE PRICE SORTED */}
          <article className="dashboard-panel">
            <div className="panel-heading">
              <div>
                <h2>Lowest Average Categories</h2>
                <p>
                  Categories sorted by average price
                </p>
              </div>
            </div>

            {sortedAverages.length > 0 ? (
              <div className="compact-list">
                {sortedAverages.slice(0, 6).map((item, index) => (
                  <div
                    className="compact-row"
                    key={item.name}
                  >
                    <div className="ranked-category">
                      <span>{index + 1}</span>
                      <strong>{item.name}</strong>
                    </div>

                    <strong>
                      {currency(item.avg_price)}
                    </strong>
                  </div>
                ))}
              </div>
            ) : (
              <p className="empty-state">
                No category data available.
              </p>
            )}
          </article>

        </section>

        {/* FIRST FIVE AND PRICE SORTING */}
        <section className="dashboard-grid lower-grid">

          <article className="dashboard-panel">
            <div className="panel-heading">
              <div>
                <h2>First Five Products</h2>
                <p>First five product records</p>
              </div>
            </div>

            <div className="compact-list">
              {firstFive.length > 0 ? (
                firstFive.map((product) => (
                  <div
                    className="compact-row"
                    key={product.id}
                  >
                    <strong>{product.name}</strong>

                    <strong>
                      {currency(product.price)}
                    </strong>
                  </div>
                ))
              ) : (
                <p className="empty-state">
                  No records available.
                </p>
              )}
            </div>
          </article>

          <article className="dashboard-panel">
            <div className="panel-heading">
              <div>
                <h2>Products by Price</h2>
                <p>
                  Lowest to highest product prices
                </p>
              </div>
            </div>

            <div className="compact-list">
              {orderedProducts.length > 0 ? (
                orderedProducts.slice(0, 6).map((product) => (
                  <div
                    className="compact-row"
                    key={product.id}
                  >
                    <strong>{product.name}</strong>

                    <strong>
                      {currency(product.price)}
                    </strong>
                  </div>
                ))
              ) : (
                <p className="empty-state">
                  No products available.
                </p>
              )}
            </div>
          </article>

        </section>

        <footer className="dashboard-footer">
          Product Dashboard · Powered by Laravel API
        </footer>

      </div>
    </main>
  );
}